FROM php:8.2-apache

# Cài đặt các extension PHP cần thiết cho Android API
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Cài đặt các extension bổ sung
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    vim \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*

# Cấu hình và cài đặt GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip

# Bật các extension
RUN docker-php-ext-enable mysqli pdo pdo_mysql gd zip

# Bật mod_rewrite cho Apache
RUN a2enmod rewrite
RUN a2enmod headers

# Cấu hình PHP cho phát triển Android API
RUN echo "upload_max_filesize = 200M" >> /usr/local/etc/php/php.ini \
    && echo "post_max_size = 200M" >> /usr/local/etc/php/php.ini \
    && echo "max_execution_time = 600" >> /usr/local/etc/php/php.ini \
    && echo "max_input_time = 600" >> /usr/local/etc/php/php.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/php.ini \
    && echo "display_errors = On" >> /usr/local/etc/php/php.ini \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/php.ini \
    && echo "error_log = /var/log/php_errors.log" >> /usr/local/etc/php/php.ini \
    && echo "date.timezone = Asia/Ho_Chi_Minh" >> /usr/local/etc/php/php.ini \
    && echo "allow_url_fopen = On" >> /usr/local/etc/php/php.ini \
    && echo "allow_url_include = Off" >> /usr/local/etc/php/php.ini

# Thiết lập múi giờ hệ thống
RUN ln -snf /usr/share/zoneinfo/Asia/Ho_Chi_Minh /etc/localtime \
    && echo Asia/Ho_Chi_Minh > /etc/timezone

# Cấu hình Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Tạo file cấu hình Apache cho API
RUN echo '<Directory /var/www/html>' > /etc/apache2/conf-available/android-api.conf \
    && echo '    Options Indexes FollowSymLinks' >> /etc/apache2/conf-available/android-api.conf \
    && echo '    AllowOverride All' >> /etc/apache2/conf-available/android-api.conf \
    && echo '    Require all granted' >> /etc/apache2/conf-available/android-api.conf \
    && echo '    Header always set Access-Control-Allow-Origin "*"' >> /etc/apache2/conf-available/android-api.conf \
    && echo '    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"' >> /etc/apache2/conf-available/android-api.conf \
    && echo '    Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"' >> /etc/apache2/conf-available/android-api.conf \
    && echo '</Directory>' >> /etc/apache2/conf-available/android-api.conf

RUN a2enconf android-api

# Set working directory
WORKDIR /var/www/html

# Tạo thư mục uploads và cấp quyền
RUN mkdir -p /var/www/html/uploads \
    && mkdir -p /var/www/html/api \
    && mkdir -p /var/www/html/config \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Tạo file info.php để kiểm tra
RUN echo "<?php phpinfo(); ?>" > /var/www/html/info.php

# Tạo file kiểm tra kết nối database
RUN echo '<?php' > /var/www/html/test_connection.php \
    && echo 'echo "<h1>🔍 Kiểm tra kết nối Android MySQL Database</h1>";' >> /var/www/html/test_connection.php \
    && echo 'try {' >> /var/www/html/test_connection.php \
    && echo '    $pdo = new PDO("mysql:host=mysql;dbname=android_db", "root", "android123");' >> /var/www/html/test_connection.php \
    && echo '    echo "<p style=\"color: green;\">✅ Kết nối database thành công!</p>";' >> /var/www/html/test_connection.php \
    && echo '    echo "<p>Database: android_db</p>";' >> /var/www/html/test_connection.php \
    && echo '    echo "<p>Host: mysql (Docker container)</p>";' >> /var/www/html/test_connection.php \
    && echo '} catch(PDOException $e) {' >> /var/www/html/test_connection.php \
    && echo '    echo "<p style=\"color: red;\">❌ Lỗi kết nối: " . $e->getMessage() . "</p>";' >> /var/www/html/test_connection.php \
    && echo '}' >> /var/www/html/test_connection.php \
    && echo '?>' >> /var/www/html/test_connection.php

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
