FROM php:8.1-apache

# Enable PDO Postgres extension
RUN docker-php-ext-install pdo pdo_pgsql

# Copy all project files into the container
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Make sure Apache reads .htaccess if you add one later
RUN a2enmod rewrite