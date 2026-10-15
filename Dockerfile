# Use a base image with PHP 8.4 and Apache
FROM php:8.4-apache

# Install necessary dependencies for Symfony and SQLite database
RUN apt-get update && apt-get install -y \
    git \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    # libfreetype6-dev \
    libsqlite3-dev \
    unzip \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_sqlite

# Set the working directory to /var/www/html
WORKDIR /var/www/html/

# Copy the project contents into the container
COPY . .

# Install Composer (dependency manager for PHP)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Symfony dependencies with Composer
RUN composer install --optimize-autoloader

# Copy Apache virtual host configuration
COPY ./config/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Configure Apache and permissions for Symfony
RUN a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && echo "Listen 8000" >> /etc/apache2/ports.conf \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Install the container startup script
COPY ./docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# Make the startup script executable
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Prepare writable directories before starting Apache
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Expose the port for Symfony to run
EXPOSE 8000

# Command to run Apache in the container
CMD ["apache2-foreground"]
