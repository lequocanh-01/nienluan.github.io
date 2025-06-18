<?php

/**
 * COMPREHENSIVE SECURITY MIDDLEWARE
 * Implements: Defense in Depth, Fail Secure, Principle of Least Privilege
 *
 * @author Security Team
 * @version 2.0
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/securityLogger.php';

class SecurityMiddleware
{
    private $db;
    private static $instance = null;
    private $logger;

    // Rate limiting - số lần truy cập tối đa trong 1 phút
    private static $rateLimits = [
        'admin' => 100,
        'manager1' => 30,
        'staff2' => 20,
        'default' => 10
    ];

    // Session timeout (phút)
    private static $sessionTimeouts = [
        'admin' => 120,
        'manager1' => 60,
        'staff2' => 60,
        'default' => 30
    ];

    public function __construct()
    {
        try {
            $this->db = Database::getInstance()->getConnection();
            $this->logger = SecurityLogger::getInstance();
        } catch (Exception $e) {
            // Fail Secure: Nếu không kết nối được database, từ chối tất cả
            error_log("SecurityMiddleware: Database connection failed - " . $e->getMessage());
            $this->db = null;
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
     * Kiểm tra quyền truy cập nghiêm ngặt với nhiều lớp bảo mật
     */
    public function checkStrictAccess($username, $module)
    {
        try {
            // 1. Kiểm tra cơ bản
            if (empty($username) || empty($module)) {
                SecurityLogger::logAccess($username, $module, false, "Empty username or module");
                return false;
            }

            // 2. Kiểm tra session timeout
            if (!$this->checkSessionTimeout($username)) {
                SecurityLogger::logAccess($username, $module, false, "Session timeout");
                return false;
            }

            // 3. Kiểm tra rate limiting
            if (!$this->checkRateLimit($username)) {
                SecurityLogger::logAccess($username, $module, false, "Rate limit exceeded");
                return false;
            }

            // 4. Kiểm tra whitelist cứng (lớp bảo mật chính)
            if (!SecurityLogger::checkWhitelist($username, $module)) {
                SecurityLogger::logAccess($username, $module, false, "Not in whitelist");
                return false;
            }

            // 5. Kiểm tra database permission (lớp bảo mật phụ)
            if (!$this->checkDatabasePermission($username, $module)) {
                SecurityLogger::logAccess($username, $module, false, "Database permission denied");
                return false;
            }

            // 6. Kiểm tra IP whitelist (nếu có)
            if (!$this->checkIPWhitelist($username)) {
                SecurityLogger::logAccess($username, $module, false, "IP not whitelisted");
                return false;
            }

            // 7. Kiểm tra thời gian làm việc cho các module nhạy cảm
            if (!$this->checkWorkingHours($username, $module)) {
                SecurityLogger::logAccess($username, $module, false, "Outside working hours");
                return false;
            }

            // Tất cả kiểm tra đều pass
            SecurityLogger::logAccess($username, $module, true, "All security checks passed");
            return true;
        } catch (Exception $e) {
            // Fail Secure: Nếu có lỗi, từ chối truy cập
            error_log("SecurityMiddleware error: " . $e->getMessage());
            SecurityLogger::logAccess($username, $module, false, "Security middleware error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra session timeout
     */
    private function checkSessionTimeout($username)
    {
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
            return true;
        }

        $timeout = self::$sessionTimeouts[$username] ?? self::$sessionTimeouts['default'];
        $timeoutSeconds = $timeout * 60;

        if (time() - $_SESSION['last_activity'] > $timeoutSeconds) {
            session_destroy();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    /**
     * Kiểm tra rate limiting
     */
    private function checkRateLimit($username)
    {
        $limit = self::$rateLimits[$username] ?? self::$rateLimits['default'];
        $key = "rate_limit_" . $username;

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 1,
                'start_time' => time()
            ];
            return true;
        }

        $data = $_SESSION[$key];

        // Reset nếu đã qua 1 phút
        if (time() - $data['start_time'] > 60) {
            $_SESSION[$key] = [
                'count' => 1,
                'start_time' => time()
            ];
            return true;
        }

        // Kiểm tra limit
        if ($data['count'] >= $limit) {
            return false;
        }

        $_SESSION[$key]['count']++;
        return true;
    }

    /**
     * Kiểm tra quyền trong database
     */
    private function checkDatabasePermission($username, $module)
    {
        // Fail Secure: Nếu không có database connection, từ chối
        if ($this->db === null) {
            return false;
        }

        try {
            // Đối với admin, luôn cho phép
            if ($username === 'admin') {
                return true;
            }

            // Kiểm tra trong bảng phân quyền
            $sql = "SELECT COUNT(*) FROM NhanVien_PhanHeQuanLy nvph
                    JOIN PhanHeQuanLy ph ON nvph.idPhanHe = ph.idPhanHe
                    JOIN nhanvien nv ON nvph.idNhanVien = nv.idNhanVien
                    JOIN user u ON nv.iduser = u.iduser
                    WHERE u.username = ? AND ph.maPhanHe = ? AND ph.trangThai = 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username, $module]);

            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            // Fail Secure: Nếu có lỗi database, từ chối
            error_log("Database permission check error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra IP whitelist
     */
    private function checkIPWhitelist($username)
    {
        // Tạm thời cho phép tất cả IP, có thể config sau
        return true;

        // Có thể implement IP whitelist ở đây
        /*
        $allowedIPs = [
            'admin' => ['127.0.0.1', '192.168.1.0/24'],
            'manager1' => ['127.0.0.1', '192.168.1.100'],
        ];

        $clientIP = SecurityLogger::getClientIP();
        $userIPs = $allowedIPs[$username] ?? [];

        return in_array($clientIP, $userIPs);
        */
    }

    /**
     * Kiểm tra thời gian làm việc cho module nhạy cảm
     */
    private function checkWorkingHours($username, $module)
    {
        // Các module nhạy cảm chỉ cho phép truy cập trong giờ làm việc
        $sensitiveModules = [
            'nhanvienview',
            'roleview',
            'vaiTroView',
            'payment_config',
            'mtonkho'
        ];

        if (!in_array($module, $sensitiveModules)) {
            return true; // Module không nhạy cảm, cho phép mọi lúc
        }

        // Admin có thể truy cập mọi lúc
        if ($username === 'admin') {
            return true;
        }

        $currentHour = (int)date('H');
        $currentDay = (int)date('N'); // 1 = Monday, 7 = Sunday

        // Chỉ cho phép từ thứ 2-6, 8h-18h
        if ($currentDay >= 6 || $currentHour < 8 || $currentHour > 18) {
            return false;
        }

        return true;
    }

    /**
     * Lấy thống kê bảo mật real-time
     */
    public function getSecurityMetrics()
    {
        return [
            'active_sessions' => $this->countActiveSessions(),
            'rate_limit_violations' => $this->countRateLimitViolations(),
            'failed_attempts_last_hour' => $this->countFailedAttempts(3600),
            'security_alerts_today' => SecurityLogger::getSecurityStats(1)['alerts'] ?? 0
        ];
    }

    private function countActiveSessions()
    {
        // Đếm session active (có thể implement với database session storage)
        return 1; // Placeholder
    }

    private function countRateLimitViolations()
    {
        // Đếm vi phạm rate limit trong session hiện tại
        $violations = 0;
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, 'rate_limit_') === 0 && is_array($value)) {
                $limit = self::$rateLimits['default'];
                if ($value['count'] >= $limit) {
                    $violations++;
                }
            }
        }
        return $violations;
    }

    private function countFailedAttempts($timeWindow)
    {
        // Đếm số lần truy cập thất bại trong khoảng thời gian
        return SecurityLogger::getSecurityStats(1)['denied'] ?? 0;
    }
}
