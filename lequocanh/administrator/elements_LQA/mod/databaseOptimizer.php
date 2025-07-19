<?php

/**
 * Database Optimizer - Phase 3 Performance Enhancement
 *
 * Comprehensive database optimization tool for analyzing and improving
 * database performance through query optimization, indexing, and caching.
 *
 * Features:
 * - Slow query analysis
 * - Index optimization recommendations
 * - Query caching system
 * - Connection pooling
 * - Performance metrics tracking
 *
 * @author Phase 3 Implementation
 * @version 1.0.0
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/logger.php';

class DatabaseOptimizer
{
    private $db;
    private $logger;
    private static $instance = null;
    private $queryCache = [];
    private $performanceMetrics = [];
    private $slowQueryThreshold = 0.1; // 100ms

    // Query cache settings
    private $cacheEnabled = true;
    private $cacheTimeout = 300; // 5 minutes
    private $maxCacheSize = 1000; // Maximum cached queries

    // Connection pool settings
    private static $connectionPool = [];
    private static $maxConnections = 10;
    private static $connectionTimeout = 30;

    private function __construct()
    {
        try {
            $this->db = Database::getInstance()->getConnection();
            $this->logger = Logger::getInstance();
            $this->initializeOptimizer();
        } catch (Exception $e) {
            error_log("DatabaseOptimizer initialization failed: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new DatabaseOptimizer();
        }
        return self::$instance;
    }

    /**
     * Initialize optimizer settings and create necessary tables
     */
    private function initializeOptimizer()
    {
        try {
            // Create performance metrics table if not exists
            $this->createPerformanceTable();

            // Create query cache table if not exists
            $this->createQueryCacheTable();

            // Enable MySQL query logging for analysis
            $this->enableQueryLogging();

            $this->logger->info("DatabaseOptimizer initialized successfully");
        } catch (Exception $e) {
            $this->logger->error("Failed to initialize DatabaseOptimizer: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create performance metrics table
     */
    private function createPerformanceTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS performance_metrics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            query_hash VARCHAR(64) NOT NULL,
            query_text TEXT NOT NULL,
            execution_time DECIMAL(10,6) NOT NULL,
            rows_examined INT DEFAULT 0,
            rows_sent INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_query_hash (query_hash),
            INDEX idx_execution_time (execution_time),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->exec($sql);
    }

    /**
     * Create query cache table
     */
    private function createQueryCacheTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS query_cache (
            cache_key VARCHAR(64) PRIMARY KEY,
            query_result LONGTEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            hit_count INT DEFAULT 0,
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->exec($sql);
    }

    /**
     * Enable MySQL slow query logging
     */
    private function enableQueryLogging()
    {
        try {
            // Enable slow query log
            $this->db->exec("SET GLOBAL slow_query_log = 'ON'");
            $this->db->exec("SET GLOBAL long_query_time = " . $this->slowQueryThreshold);
            $this->db->exec("SET GLOBAL log_queries_not_using_indexes = 'ON'");
        } catch (Exception $e) {
            // Log warning but don't fail - might not have privileges
            $this->logger->warning("Could not enable query logging: " . $e->getMessage());
        }
    }

    /**
     * Execute optimized query with caching and performance tracking
     */
    public function executeQuery($sql, $params = [], $useCache = true)
    {
        $startTime = microtime(true);
        $cacheKey = $this->generateCacheKey($sql, $params);

        // Try cache first if enabled
        if ($useCache && $this->cacheEnabled) {
            $cachedResult = $this->getCachedResult($cacheKey);
            if ($cachedResult !== null) {
                $this->updateCacheHitCount($cacheKey);
                return $cachedResult;
            }
        }

        try {
            // Execute query
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $executionTime = microtime(true) - $startTime;

            // Log performance metrics
            $this->logQueryPerformance($sql, $params, $executionTime, $stmt->rowCount());

            // Cache result if enabled
            if ($useCache && $this->cacheEnabled && $executionTime < 1.0) { // Only cache fast queries
                $this->cacheResult($cacheKey, $result);
            }

            return $result;
        } catch (Exception $e) {
            $executionTime = microtime(true) - $startTime;
            $this->logger->error("Query execution failed", [
                'sql' => $sql,
                'params' => $params,
                'execution_time' => $executionTime,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate cache key for query and parameters
     */
    private function generateCacheKey($sql, $params)
    {
        return hash('sha256', $sql . serialize($params));
    }

    /**
     * Get cached query result
     */
    private function getCachedResult($cacheKey)
    {
        try {
            $sql = "SELECT query_result, expires_at FROM query_cache
                    WHERE cache_key = ? AND expires_at > NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$cacheKey]);
            $cached = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cached) {
                return json_decode($cached['query_result'], true);
            }
        } catch (Exception $e) {
            $this->logger->warning("Cache retrieval failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Cache query result
     */
    private function cacheResult($cacheKey, $result)
    {
        try {
            // Clean old cache entries if we're at max size
            $this->cleanOldCache();

            $expiresAt = date('Y-m-d H:i:s', time() + $this->cacheTimeout);
            $sql = "INSERT INTO query_cache (cache_key, query_result, expires_at)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    query_result = VALUES(query_result),
                    expires_at = VALUES(expires_at),
                    hit_count = 0";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$cacheKey, json_encode($result), $expiresAt]);
        } catch (Exception $e) {
            $this->logger->warning("Cache storage failed: " . $e->getMessage());
        }
    }

    /**
     * Update cache hit count
     */
    private function updateCacheHitCount($cacheKey)
    {
        try {
            $sql = "UPDATE query_cache SET hit_count = hit_count + 1 WHERE cache_key = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$cacheKey]);
        } catch (Exception $e) {
            $this->logger->warning("Cache hit count update failed: " . $e->getMessage());
        }
    }

    /**
     * Clean old cache entries
     */
    private function cleanOldCache()
    {
        try {
            // Remove expired entries
            $this->db->exec("DELETE FROM query_cache WHERE expires_at < NOW()");

            // Remove oldest entries if we're at max size
            $countSql = "SELECT COUNT(*) as count FROM query_cache";
            $stmt = $this->db->query($countSql);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($count >= $this->maxCacheSize) {
                $deleteCount = $count - $this->maxCacheSize + 100; // Remove extra 100
                $this->db->exec("DELETE FROM query_cache ORDER BY created_at ASC LIMIT $deleteCount");
            }
        } catch (Exception $e) {
            $this->logger->warning("Cache cleanup failed: " . $e->getMessage());
        }
    }

    /**
     * Log query performance metrics
     */
    private function logQueryPerformance($sql, $params, $executionTime, $rowCount)
    {
        try {
            $queryHash = hash('md5', $sql);

            // Only log if execution time exceeds threshold or it's a slow query
            if ($executionTime > $this->slowQueryThreshold) {
                $insertSql = "INSERT INTO performance_metrics
                             (query_hash, query_text, execution_time, rows_sent)
                             VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($insertSql);
                $stmt->execute([$queryHash, $sql, $executionTime, $rowCount]);

                // Log warning for very slow queries
                if ($executionTime > 1.0) {
                    $this->logger->warning("Slow query detected", [
                        'execution_time' => $executionTime,
                        'sql' => substr($sql, 0, 200) . '...',
                        'row_count' => $rowCount
                    ]);
                }
            }
        } catch (Exception $e) {
            $this->logger->warning("Performance logging failed: " . $e->getMessage());
        }
    }

    /**
     * Analyze slow queries and provide optimization recommendations
     */
    public function analyzeSlowQueries($limit = 20)
    {
        try {
            $sql = "SELECT query_hash, query_text,
                           AVG(execution_time) as avg_time,
                           MAX(execution_time) as max_time,
                           COUNT(*) as frequency,
                           AVG(rows_sent) as avg_rows
                    FROM performance_metrics
                    WHERE execution_time > ?
                    GROUP BY query_hash
                    ORDER BY avg_time DESC, frequency DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->slowQueryThreshold, $limit]);
            $slowQueries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $analysis = [];
            foreach ($slowQueries as $query) {
                $analysis[] = [
                    'query' => $query['query_text'],
                    'avg_time' => round($query['avg_time'], 4),
                    'max_time' => round($query['max_time'], 4),
                    'frequency' => $query['frequency'],
                    'avg_rows' => round($query['avg_rows']),
                    'recommendations' => $this->generateOptimizationRecommendations($query['query_text'])
                ];
            }

            return $analysis;
        } catch (Exception $e) {
            $this->logger->error("Slow query analysis failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate optimization recommendations for a query
     */
    private function generateOptimizationRecommendations($sql)
    {
        $recommendations = [];
        $sqlLower = strtolower($sql);

        // Check for missing WHERE clauses
        if (strpos($sqlLower, 'select') !== false && strpos($sqlLower, 'where') === false) {
            $recommendations[] = "Consider adding WHERE clause to limit result set";
        }

        // Check for SELECT *
        if (strpos($sqlLower, 'select *') !== false) {
            $recommendations[] = "Avoid SELECT * - specify only needed columns";
        }

        // Check for missing LIMIT
        if (strpos($sqlLower, 'select') !== false && strpos($sqlLower, 'limit') === false) {
            $recommendations[] = "Consider adding LIMIT clause for large result sets";
        }

        // Check for complex JOINs
        $joinCount = substr_count($sqlLower, 'join');
        if ($joinCount > 3) {
            $recommendations[] = "Complex JOINs detected - consider query restructuring or denormalization";
        }

        // Check for subqueries
        if (strpos($sqlLower, '(select') !== false) {
            $recommendations[] = "Subqueries detected - consider using JOINs for better performance";
        }

        // Check for ORDER BY without LIMIT
        if (strpos($sqlLower, 'order by') !== false && strpos($sqlLower, 'limit') === false) {
            $recommendations[] = "ORDER BY without LIMIT can be expensive - consider adding LIMIT";
        }

        return $recommendations;
    }

    /**
     * Suggest indexes for tables based on query patterns
     */
    public function suggestIndexes($tableName = null)
    {
        try {
            $suggestions = [];

            if ($tableName) {
                $suggestions[$tableName] = $this->analyzeTableIndexes($tableName);
            } else {
                // Analyze all tables
                $tables = $this->getAllTables();
                foreach ($tables as $table) {
                    $tableIndexes = $this->analyzeTableIndexes($table);
                    if (!empty($tableIndexes)) {
                        $suggestions[$table] = $tableIndexes;
                    }
                }
            }

            return $suggestions;
        } catch (Exception $e) {
            $this->logger->error("Index suggestion failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Analyze indexes for a specific table
     */
    private function analyzeTableIndexes($tableName)
    {
        $suggestions = [];

        try {
            // Get table structure
            $stmt = $this->db->query("DESCRIBE `$tableName`");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get existing indexes
            $stmt = $this->db->query("SHOW INDEX FROM `$tableName`");
            $existingIndexes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Analyze query patterns for this table
            $queryPatterns = $this->getQueryPatternsForTable($tableName);

            // Suggest indexes based on WHERE clauses
            foreach ($queryPatterns as $pattern) {
                $whereColumns = $this->extractWhereColumns($pattern['query_text']);
                foreach ($whereColumns as $column) {
                    if (!$this->hasIndexOnColumn($existingIndexes, $column)) {
                        $suggestions[] = [
                            'type' => 'single_column',
                            'column' => $column,
                            'reason' => 'Frequently used in WHERE clauses',
                            'sql' => "ALTER TABLE `$tableName` ADD INDEX idx_{$column} (`$column`)"
                        ];
                    }
                }
            }

            // Suggest composite indexes for multi-column WHERE clauses
            $compositeColumns = $this->findCompositeIndexOpportunities($queryPatterns);
            foreach ($compositeColumns as $columns) {
                if (!$this->hasCompositeIndex($existingIndexes, $columns)) {
                    $columnList = implode(', ', array_map(function ($col) {
                        return "`$col`";
                    }, $columns));
                    $indexName = 'idx_' . implode('_', $columns);
                    $suggestions[] = [
                        'type' => 'composite',
                        'columns' => $columns,
                        'reason' => 'Multi-column WHERE conditions detected',
                        'sql' => "ALTER TABLE `$tableName` ADD INDEX $indexName ($columnList)"
                    ];
                }
            }
        } catch (Exception $e) {
            $this->logger->warning("Table index analysis failed for $tableName: " . $e->getMessage());
        }

        return $suggestions;
    }

    /**
     * Get all tables in the database
     */
    private function getAllTables()
    {
        $stmt = $this->db->query("SHOW TABLES");
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        return $tables;
    }

    /**
     * Get query patterns for a specific table
     */
    private function getQueryPatternsForTable($tableName)
    {
        try {
            $sql = "SELECT DISTINCT query_text, COUNT(*) as frequency
                    FROM performance_metrics
                    WHERE query_text LIKE ?
                    GROUP BY query_text
                    ORDER BY frequency DESC
                    LIMIT 50";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(["%$tableName%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Extract WHERE clause columns from SQL
     */
    private function extractWhereColumns($sql)
    {
        $columns = [];
        $sqlLower = strtolower($sql);

        // Simple regex to find WHERE conditions
        if (preg_match_all('/where\s+.*?(\w+)\s*[=<>!]/i', $sql, $matches)) {
            foreach ($matches[1] as $column) {
                if (!in_array($column, ['and', 'or', 'not', 'in', 'like'])) {
                    $columns[] = $column;
                }
            }
        }

        return array_unique($columns);
    }

    /**
     * Check if table has index on specific column
     */
    private function hasIndexOnColumn($existingIndexes, $column)
    {
        foreach ($existingIndexes as $index) {
            if (strtolower($index['Column_name']) === strtolower($column)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if table has composite index on columns
     */
    private function hasCompositeIndex($existingIndexes, $columns)
    {
        $indexGroups = [];
        foreach ($existingIndexes as $index) {
            $indexGroups[$index['Key_name']][] = strtolower($index['Column_name']);
        }

        $columnsLower = array_map('strtolower', $columns);
        sort($columnsLower);

        foreach ($indexGroups as $indexColumns) {
            sort($indexColumns);
            if ($indexColumns === $columnsLower) {
                return true;
            }
        }
        return false;
    }

    /**
     * Find opportunities for composite indexes
     */
    private function findCompositeIndexOpportunities($queryPatterns)
    {
        $compositeOpportunities = [];

        foreach ($queryPatterns as $pattern) {
            $whereColumns = $this->extractWhereColumns($pattern['query_text']);
            if (count($whereColumns) > 1) {
                sort($whereColumns);
                $key = implode('_', $whereColumns);
                if (!isset($compositeOpportunities[$key])) {
                    $compositeOpportunities[$key] = $whereColumns;
                }
            }
        }

        return array_values($compositeOpportunities);
    }

    /**
     * Get performance statistics
     */
    public function getPerformanceStats()
    {
        try {
            $stats = [];

            // Query performance stats
            $sql = "SELECT
                        COUNT(*) as total_queries,
                        AVG(execution_time) as avg_execution_time,
                        MAX(execution_time) as max_execution_time,
                        COUNT(CASE WHEN execution_time > ? THEN 1 END) as slow_queries
                    FROM performance_metrics
                    WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->slowQueryThreshold]);
            $stats['queries'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // Cache performance stats
            $sql = "SELECT
                        COUNT(*) as total_cached,
                        SUM(hit_count) as total_hits,
                        AVG(hit_count) as avg_hits_per_query
                    FROM query_cache
                    WHERE expires_at > NOW()";

            $stmt = $this->db->query($sql);
            $stats['cache'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // Calculate cache hit ratio
            $totalQueries = $stats['queries']['total_queries'] ?? 0;
            $totalHits = $stats['cache']['total_hits'] ?? 0;
            $stats['cache']['hit_ratio'] = $totalQueries > 0 ? round(($totalHits / $totalQueries) * 100, 2) : 0;

            return $stats;
        } catch (Exception $e) {
            $this->logger->error("Performance stats retrieval failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear query cache
     */
    public function clearCache()
    {
        try {
            $this->db->exec("DELETE FROM query_cache");
            $this->logger->info("Query cache cleared successfully");
            return true;
        } catch (Exception $e) {
            $this->logger->error("Cache clear failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Optimize database tables
     */
    public function optimizeTables($tables = null)
    {
        try {
            $results = [];

            if ($tables === null) {
                $tables = $this->getAllTables();
            } elseif (is_string($tables)) {
                $tables = [$tables];
            }

            foreach ($tables as $table) {
                try {
                    $this->db->exec("OPTIMIZE TABLE `$table`");
                    $results[$table] = 'success';
                    $this->logger->info("Table $table optimized successfully");
                } catch (Exception $e) {
                    $results[$table] = 'failed: ' . $e->getMessage();
                    $this->logger->warning("Table $table optimization failed: " . $e->getMessage());
                }
            }

            return $results;
        } catch (Exception $e) {
            $this->logger->error("Table optimization failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get database size information
     */
    public function getDatabaseSize()
    {
        try {
            $sql = "SELECT
                        table_name,
                        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                        table_rows
                    FROM information_schema.TABLES
                    WHERE table_schema = DATABASE()
                    ORDER BY (data_length + index_length) DESC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logger->error("Database size retrieval failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate comprehensive optimization report
     */
    public function generateOptimizationReport()
    {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'performance_stats' => $this->getPerformanceStats(),
            'slow_queries' => $this->analyzeSlowQueries(10),
            'index_suggestions' => $this->suggestIndexes(),
            'database_size' => $this->getDatabaseSize(),
            'recommendations' => []
        ];

        // Generate general recommendations
        $stats = $report['performance_stats'];
        if (isset($stats['queries']['slow_queries']) && $stats['queries']['slow_queries'] > 0) {
            $report['recommendations'][] = "Found {$stats['queries']['slow_queries']} slow queries - consider optimization";
        }

        if (isset($stats['cache']['hit_ratio']) && $stats['cache']['hit_ratio'] < 50) {
            $report['recommendations'][] = "Cache hit ratio is low ({$stats['cache']['hit_ratio']}%) - consider increasing cache timeout";
        }

        if (count($report['index_suggestions']) > 0) {
            $totalSuggestions = array_sum(array_map('count', $report['index_suggestions']));
            $report['recommendations'][] = "Found $totalSuggestions index optimization opportunities";
        }

        return $report;
    }
}
