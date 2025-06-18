<?php
/**
 * COMPREHENSIVE SECURITY CONFIGURATION
 * Centralized security settings for the entire application
 * 
 * @author Security Team
 * @version 2.0
 */

class SecurityConfig {
    
    // PRINCIPLE OF LEAST PRIVILEGE - User Whitelist
    const USER_WHITELIST = [
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
    
    // SENSITIVE MODULES - Require special monitoring
    const SENSITIVE_MODULES = [
        'nhanvienview',
        'roleview', 
        'vaiTroView',
        'danhSachVaiTroView',
        'payment_config',
        'mtonkho',
        'mphieunhap',
        'database_config',
        'system_config'
    ];
    
    // RATE LIMITING - Requests per minute
    const RATE_LIMITS = [
        'admin' => 100,
        'manager1' => 30,
        'staff2' => 20,
        'default' => 10
    ];
    
    // SESSION TIMEOUTS - Minutes
    const SESSION_TIMEOUTS = [
        'admin' => 120,
        'manager1' => 60, 
        'staff2' => 60,
        'default' => 30
    ];
    
    // WORKING HOURS - For sensitive modules
    const WORKING_HOURS = [
        'start' => 8,  // 8 AM
        'end' => 18,   // 6 PM
        'days' => [1, 2, 3, 4, 5] // Monday to Friday
    ];
    
    // IP WHITELIST - Can be expanded
    const IP_WHITELIST = [
        'admin' => ['127.0.0.1', '::1'],
        'manager1' => ['127.0.0.1', '::1'],
        'staff2' => ['127.0.0.1', '::1']
    ];
    
    // SECURITY LEVELS
    const SECURITY_LEVELS = [
        'LOW' => 1,
        'MEDIUM' => 2, 
        'HIGH' => 3,
        'CRITICAL' => 4
    ];
    
    // MODULE SECURITY LEVELS
    const MODULE_SECURITY_LEVELS = [
        'userprofile' => self::SECURITY_LEVELS['LOW'],
        'userUpdateProfile' => self::SECURITY_LEVELS['LOW'],
        'thongbao' => self::SECURITY_LEVELS['LOW'],
        'baocaoview' => self::SECURITY_LEVELS['MEDIUM'],
        'doanhThuView' => self::SECURITY_LEVELS['MEDIUM'],
        'sanPhamBanChayView' => self::SECURITY_LEVELS['MEDIUM'],
        'loiNhuanView' => self::SECURITY_LEVELS['MEDIUM'],
        'hanghoaview' => self::SECURITY_LEVELS['MEDIUM'],
        'dongiaview' => self::SECURITY_LEVELS['MEDIUM'],
        'orders' => self::SECURITY_LEVELS['MEDIUM'],
        'nhanvienview' => self::SECURITY_LEVELS['HIGH'],
        'roleview' => self::SECURITY_LEVELS['HIGH'],
        'vaiTroView' => self::SECURITY_LEVELS['HIGH'],
        'payment_config' => self::SECURITY_LEVELS['CRITICAL'],
        'mtonkho' => self::SECURITY_LEVELS['HIGH'],
        'mphieunhap' => self::SECURITY_LEVELS['HIGH']
    ];
    
    // ALERT THRESHOLDS
    const ALERT_THRESHOLDS = [
        'failed_attempts_per_hour' => 5,
        'rate_limit_violations_per_hour' => 3,
        'off_hours_access_attempts' => 1,
        'sensitive_module_access_by_non_admin' => 1
    ];
    
    // LOG RETENTION - Days
    const LOG_RETENTION = [
        'access_log' => 90,
        'alert_log' => 365,
        'audit_log' => 2555 // 7 years
    ];
    
    /**
     * Get user whitelist for specific user
     */
    public static function getUserWhitelist($username) {
        return self::USER_WHITELIST[$username] ?? [];
    }
    
    /**
     * Check if user has access to module based on whitelist
     */
    public static function checkUserWhitelist($username, $module) {
        $whitelist = self::getUserWhitelist($username);
        
        // Admin has access to everything
        if (in_array('*', $whitelist)) {
            return true;
        }
        
        return in_array($module, $whitelist);
    }
    
    /**
     * Get rate limit for user
     */
    public static function getRateLimit($username) {
        return self::RATE_LIMITS[$username] ?? self::RATE_LIMITS['default'];
    }
    
    /**
     * Get session timeout for user
     */
    public static function getSessionTimeout($username) {
        return self::SESSION_TIMEOUTS[$username] ?? self::SESSION_TIMEOUTS['default'];
    }
    
    /**
     * Check if module is sensitive
     */
    public static function isSensitiveModule($module) {
        return in_array($module, self::SENSITIVE_MODULES);
    }
    
    /**
     * Get security level for module
     */
    public static function getModuleSecurityLevel($module) {
        return self::MODULE_SECURITY_LEVELS[$module] ?? self::SECURITY_LEVELS['MEDIUM'];
    }
    
    /**
     * Check if current time is within working hours
     */
    public static function isWorkingHours() {
        $currentHour = (int)date('H');
        $currentDay = (int)date('N'); // 1 = Monday, 7 = Sunday
        
        return in_array($currentDay, self::WORKING_HOURS['days']) &&
               $currentHour >= self::WORKING_HOURS['start'] &&
               $currentHour <= self::WORKING_HOURS['end'];
    }
    
    /**
     * Check if IP is whitelisted for user
     */
    public static function isIPWhitelisted($username, $ip) {
        $whitelist = self::IP_WHITELIST[$username] ?? [];
        return empty($whitelist) || in_array($ip, $whitelist);
    }
    
    /**
     * Get alert threshold
     */
    public static function getAlertThreshold($type) {
        return self::ALERT_THRESHOLDS[$type] ?? 1;
    }
    
    /**
     * Get log retention period
     */
    public static function getLogRetention($type) {
        return self::LOG_RETENTION[$type] ?? 30;
    }
    
    /**
     * Validate security configuration
     */
    public static function validateConfig() {
        $errors = [];
        
        // Check if all users in whitelist have valid modules
        foreach (self::USER_WHITELIST as $user => $modules) {
            if (!is_array($modules)) {
                $errors[] = "Invalid modules array for user: $user";
            }
        }
        
        // Check if rate limits are positive numbers
        foreach (self::RATE_LIMITS as $user => $limit) {
            if (!is_int($limit) || $limit <= 0) {
                $errors[] = "Invalid rate limit for user: $user";
            }
        }
        
        // Check if session timeouts are positive numbers
        foreach (self::SESSION_TIMEOUTS as $user => $timeout) {
            if (!is_int($timeout) || $timeout <= 0) {
                $errors[] = "Invalid session timeout for user: $user";
            }
        }
        
        return $errors;
    }
    
    /**
     * Get security policy summary
     */
    public static function getSecurityPolicySummary() {
        return [
            'total_users_in_whitelist' => count(self::USER_WHITELIST),
            'total_sensitive_modules' => count(self::SENSITIVE_MODULES),
            'working_hours' => self::WORKING_HOURS['start'] . ':00 - ' . self::WORKING_HOURS['end'] . ':00',
            'working_days' => implode(', ', self::WORKING_HOURS['days']),
            'max_rate_limit' => max(self::RATE_LIMITS),
            'min_rate_limit' => min(self::RATE_LIMITS),
            'max_session_timeout' => max(self::SESSION_TIMEOUTS),
            'min_session_timeout' => min(self::SESSION_TIMEOUTS),
            'log_retention_days' => self::LOG_RETENTION
        ];
    }
    
    /**
     * Export configuration for backup
     */
    public static function exportConfig() {
        return [
            'user_whitelist' => self::USER_WHITELIST,
            'sensitive_modules' => self::SENSITIVE_MODULES,
            'rate_limits' => self::RATE_LIMITS,
            'session_timeouts' => self::SESSION_TIMEOUTS,
            'working_hours' => self::WORKING_HOURS,
            'ip_whitelist' => self::IP_WHITELIST,
            'security_levels' => self::SECURITY_LEVELS,
            'module_security_levels' => self::MODULE_SECURITY_LEVELS,
            'alert_thresholds' => self::ALERT_THRESHOLDS,
            'log_retention' => self::LOG_RETENTION,
            'export_timestamp' => date('Y-m-d H:i:s'),
            'version' => '2.0'
        ];
    }
    
    /**
     * Get security recommendations
     */
    public static function getSecurityRecommendations() {
        return [
            'Enable two-factor authentication',
            'Regular security audits (weekly)',
            'Update passwords every 90 days', 
            'Monitor failed login attempts',
            'Backup security logs daily',
            'Review user permissions monthly',
            'Implement IP-based restrictions',
            'Use HTTPS for all communications',
            'Regular penetration testing',
            'Security awareness training for staff'
        ];
    }
}
?>
