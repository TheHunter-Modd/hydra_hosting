FROM php:8.1-apache

# Install PostgreSQL client libraries FIRST
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean

# Copy all project files into the container
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Enable Apache mod_rewrite
RUN a2enmod rewrite