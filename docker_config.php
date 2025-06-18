<?php
// Cấu hình Docker Environment cho các dự án

// Dự án LeQuocAnh (hiện tại)
define('LEQUOCANH_WEB_PORT', '8888');
define('LEQUOCANH_PHPMYADMIN_PORT', '8889');
define('LEQUOCANH_WEB_URL', 'http://localhost:' . LEQUOCANH_WEB_PORT);
define('LEQUOCANH_PHPMYADMIN_URL', 'http://localhost:' . LEQUOCANH_PHPMYADMIN_PORT);

// Dự án Android with MySQL (mới)
define('ANDROID_WEB_PORT', '8890');
define('ANDROID_PHPMYADMIN_PORT', '8891');
define('ANDROID_WEB_URL', 'http://localhost:' . ANDROID_WEB_PORT);
define('ANDROID_PHPMYADMIN_URL', 'http://localhost:' . ANDROID_PHPMYADMIN_PORT);

// Container info
$docker_containers = [
    'lequocanh' => [
        'web' => 'apache-php-1',
        'database' => 'mysql-1',
        'phpmyadmin' => 'phpmyadmin'
    ],
    'android' => [
        'web' => 'android-apache-php',
        'database' => 'android-mysql',
        'phpmyadmin' => 'android-phpmyadmin'
    ]
];

echo "=== DỰ ÁN LEQUOCANH ===\n";
echo "Web URL: " . LEQUOCANH_WEB_URL . "\n";
echo "phpMyAdmin URL: " . LEQUOCANH_PHPMYADMIN_URL . "\n\n";

echo "=== DỰ ÁN ANDROID WITH MYSQL ===\n";
echo "Web URL: " . ANDROID_WEB_URL . "\n";
echo "phpMyAdmin URL: " . ANDROID_PHPMYADMIN_URL . "\n";
