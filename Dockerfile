FROM php:8.2-apache

# Enable mod_rewrite (needed for clean URLs)
RUN a2enmod rewrite

# Install curl extension (used by checkout.php to call ToyyibPay API)
RUN docker-php-ext-install curl

# Allow .htaccess to override Apache config
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy all project files into Apache web root
COPY . /var/www/html/

# Give Apache permission to write log files (orders.log, payments.log)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
