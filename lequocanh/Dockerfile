FROM php:8.2-apache

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

# Enable Apache mod_rewrite
RUN a2enmod rewrite

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