FROM php:8.2-apache

# Enable mod_rewrite (needed for clean URLs)
RUN a2enmod rewrite

# Install libcurl system library, then the PHP curl extension
RUN apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

# Allow .htaccess to override Apache config
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy all project files into Apache web root
COPY . /var/www/html/

# Give Apache permission to write log files (orders.log, payments.log)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
