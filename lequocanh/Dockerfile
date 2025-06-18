FROM php:apache

# Cài đặt các extension PHP cần thiết
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Bật các extension
RUN docker-php-ext-enable mysqli pdo pdo_mysql

# Cài đặt một số tiện ích hữu ích
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    vim \
    && rm -rf /var/lib/apt/lists/*

# Bật mod_rewrite
RUN a2enmod rewrite

# Cấu hình PHP cho phát triển
RUN echo "upload_max_filesize = 128M" >> /usr/local/etc/php/php.ini \
    && echo "post_max_size = 128M" >> /usr/local/etc/php/php.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/php.ini \
    && echo "display_errors = On" >> /usr/local/etc/php/php.ini \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini \
    && echo "date.timezone = Asia/Ho_Chi_Minh" >> /usr/local/etc/php/php.ini

# Thiết lập múi giờ hệ thống
RUN ln -snf /usr/share/zoneinfo/Asia/Ho_Chi_Minh /etc/localtime \
    && echo Asia/Ho_Chi_Minh > /etc/timezone

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# Copy custom php.ini
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Set upload directory permissions
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 755 /var/www/html/uploads

# Copy application files
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Install PHP info
RUN echo "<?php phpinfo(); ?>" > /var/www/html/info.php

# Create default index.php if it doesn't exist
RUN echo '<?php echo "<h1>Docker PHP đã hoạt động!</h1>"; ?>' > /var/www/html/docker-ready.php

# Configure Apache to display error messages
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN echo "php_flag display_errors on" >> /etc/apache2/conf-available/php.conf
RUN echo "php_value error_reporting E_ALL" >> /etc/apache2/conf-available/php.conf
RUN a2enconf php

# Update Apache configuration to use the correct document root
RUN sed -i 's!/var/www/html!/var/www/html!g' /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]