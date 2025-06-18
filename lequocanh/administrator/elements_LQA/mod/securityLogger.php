<?php

/**
 * COMPREHENSIVE SECURITY LOGGER
 * Implements: Audit Trail, Fail Secure, Defense in Depth
 *
 * @author Security Team
 * @version 2.0
 */

class SecurityLogger
{
    private static $logFile;
    private static $alertFile;
    private static $instance = null;

    // WHITELIST CỨNG - Principle of Least Privilege
    private static $userWhitelist = [
        'admin' => ['*'], // Admin có quyền tất cả
        'manager1' => [
            'userprofile',
            'userUpdateProfile',
            'thongbao',
            'baocaoview',
            'doanhThuView',
            'sanPhamBanChayView',
            'loiNhuanView'
        ],
        'staff2' => [
            'userprofile',
            'userUpdateProfile',
            'thongbao',
            'hanghoaview',
            'dongiaview',
            'orders'
        ]
    ];

    // Các module nhạy cảm cần cảnh báo đặc biệt
    private static $sensitiveModules = [
        'nhanvienview',
        'roleview',
        'vaiTroView',
        'danhSachVaiTroView',
        'payment_config',
        'mtonkho',
        'mphieunhap'
    ];

    public function __construct()
    {
        self::$logFile = __DIR__ . "/security_access.log";
        self::$alertFile = __DIR__ . "/security_alerts.log";

        // Tạo file log nếu chưa có
        if (!file_exists(self::$logFile)) {
            file_put_contents(self::$logFile, "=== SECURITY ACCESS LOG STARTED ===" . PHP_EOL);
        }
        if (!file_exists(self::$alertFile)) {
            file_put_contents(self::$alertFile, "=== SECURITY ALERTS LOG STARTED ===" . PHP_EOL);
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Ghi log truy cập với đầy đủ thông tin
     */
    public static function logAccess($username, $module, $hasAccess, $reason = "", $additionalInfo = [])
    {
        $logger = self::getInstance();

        $timestamp = date("Y-m-d H:i:s");
        $ip = self::getClientIP();
        $userAgent = $_SERVER["HTTP_USER_AGENT"] ?? "unknown";
        $sessionId = session_id() ?? "no-session";
        $referer = $_SERVER["HTTP_REFERER"] ?? "direct";

        // Thông tin cơ bản
        $logData = [
            'timestamp' => $timestamp,
            'username' => $username,
            'module' => $module,
            'access' => $hasAccess ? 'GRANTED' : 'DENIED',
            'reason' => $reason,
            'ip' => $ip,
            'session_id' => $sessionId,
            'user_agent' => $userAgent,
            'referer' => $referer,
            'additional' => $additionalInfo
        ];

        // Format log entry
        $logEntry = sprintf(
            "[%s] USER: %s | MODULE: %s | ACCESS: %s | REASON: %s | IP: %s | SESSION: %s | REFERER: %s%s",
            $timestamp,
            $username,
            $module,
            $hasAccess ? 'GRANTED' : 'DENIED',
            $reason,
            $ip,
            $sessionId,
            $referer,
            PHP_EOL
        );

        // Ghi log
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);

        // Kiểm tra và cảnh báo
        self::checkAndAlert($username, $module, $hasAccess, $logData);

        return $logData;
    }

    /**
     * Kiểm tra whitelist cứng
     */
    public static function checkWhitelist($username, $module)
    {
        if (!isset(self::$userWhitelist[$username])) {
            return false;
        }

        $allowedModules = self::$userWhitelist[$username];

        // Admin có quyền tất cả
        if (in_array('*', $allowedModules)) {
            return true;
        }

        return in_array($module, $allowedModules);
    }

    /**
     * Kiểm tra và cảnh báo bất thường
     */
    private static function checkAndAlert($username, $module, $hasAccess, $logData)
    {
        $alerts = [];

        // 1. Truy cập không được phép theo whitelist
        if ($hasAccess && !self::checkWhitelist($username, $module)) {
            $alerts[] = "UNAUTHORIZED_ACCESS: User $username accessed $module without whitelist permission";
        }

        // 2. Truy cập module nhạy cảm
        if ($hasAccess && in_array($module, self::$sensitiveModules)) {
            $alerts[] = "SENSITIVE_MODULE_ACCESS: User $username accessed sensitive module $module";
        }

        // 3. Truy cập từ IP lạ (có thể mở rộng với database IP whitelist)
        $suspiciousIPs = ['127.0.0.1']; // Tạm thời để trống, có thể config sau
        // if (in_array($logData['ip'], $suspiciousIPs)) {
        //     $alerts[] = "SUSPICIOUS_IP: Access from suspicious IP " . $logData['ip'];
        // }

        // 4. Nhiều lần truy cập thất bại liên tiếp
        if (!$hasAccess) {
            $failedAttempts = self::countRecentFailedAttempts($username, $module);
            if ($failedAttempts >= 3) {
                $alerts[] = "MULTIPLE_FAILED_ATTEMPTS: User $username failed to access $module $failedAttempts times";
            }
        }

        // 5. Truy cập ngoài giờ làm việc (8h-18h)
        $currentHour = (int)date('H');
        if ($hasAccess && ($currentHour < 8 || $currentHour > 18)) {
            $alerts[] = "OFF_HOURS_ACCESS: User $username accessed $module at $currentHour:00";
        }

        // Ghi cảnh báo
        foreach ($alerts as $alert) {
            self::writeAlert($alert, $logData);
        }
    }

    /**
     * Ghi cảnh báo bảo mật
     */
    private static function writeAlert($alertMessage, $logData)
    {
        $timestamp = $logData['timestamp'];
        $alertEntry = sprintf(
            "[%s] 🚨 SECURITY ALERT: %s | USER: %s | MODULE: %s | IP: %s%s",
            $timestamp,
            $alertMessage,
            $logData['username'],
            $logData['module'],
            $logData['ip'],
            PHP_EOL
        );

        file_put_contents(self::$alertFile, $alertEntry, FILE_APPEND | LOCK_EX);

        // Có thể thêm email alert hoặc notification khác ở đây
        // self::sendEmailAlert($alertMessage, $logData);
    }

    /**
     * Đếm số lần truy cập thất bại gần đây
     */
    private static function countRecentFailedAttempts($username, $module)
    {
        if (!file_exists(self::$logFile)) {
            return 0;
        }

        $logContent = file_get_contents(self::$logFile);
        $lines = explode(PHP_EOL, $logContent);

        $failedCount = 0;
        $timeLimit = time() - 300; // 5 phút gần đây

        foreach (array_reverse($lines) as $line) {
            if (empty($line)) continue;

            if (
                strpos($line, "USER: $username") !== false &&
                strpos($line, "MODULE: $module") !== false &&
                strpos($line, "ACCESS: DENIED") !== false
            ) {

                // Extract timestamp và kiểm tra thời gian
                if (preg_match('/\[([\d\-\s:]+)\]/', $line, $matches)) {
                    $logTime = strtotime($matches[1]);
                    if ($logTime >= $timeLimit) {
                        $failedCount++;
                    } else {
                        break; // Đã quá thời gian limit
                    }
                }
            }
        }

        return $failedCount;
    }

    /**
     * Lấy IP thực của client
     */
    private static function getClientIP()
    {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Lấy thống kê bảo mật
     */
    public static function getSecurityStats($days = 7)
    {
        if (!file_exists(self::$logFile)) {
            return [];
        }

        $logContent = file_get_contents(self::$logFile);
        $lines = explode(PHP_EOL, $logContent);

        $stats = [
            'total_access' => 0,
            'granted' => 0,
            'denied' => 0,
            'users' => [],
            'modules' => [],
            'alerts' => 0
        ];

        $timeLimit = time() - ($days * 24 * 3600);

        foreach ($lines as $line) {
            if (empty($line) || strpos($line, 'USER:') === false) continue;

            // Extract timestamp
            if (preg_match('/\[([\d\-\s:]+)\]/', $line, $matches)) {
                $logTime = strtotime($matches[1]);
                if ($logTime < $timeLimit) continue;
            }

            $stats['total_access']++;

            if (strpos($line, 'ACCESS: GRANTED') !== false) {
                $stats['granted']++;
            } else {
                $stats['denied']++;
            }

            // Extract user và module
            if (preg_match('/USER: (\w+)/', $line, $matches)) {
                $user = $matches[1];
                $stats['users'][$user] = ($stats['users'][$user] ?? 0) + 1;
            }

            if (preg_match('/MODULE: (\w+)/', $line, $matches)) {
                $module = $matches[1];
                $stats['modules'][$module] = ($stats['modules'][$module] ?? 0) + 1;
            }
        }

        // Đếm alerts
        if (file_exists(self::$alertFile)) {
            $alertContent = file_get_contents(self::$alertFile);
            $alertLines = explode(PHP_EOL, $alertContent);
            foreach ($alertLines as $line) {
                if (strpos($line, '🚨') !== false) {
                    $stats['alerts']++;
                }
            }
        }

        return $stats;
    }
}

// Backward compatibility function
function logSecurityAccess($username, $module, $hasAccess, $reason = "")
{
    return SecurityLogger::logAccess($username, $module, $hasAccess, $reason);
}
