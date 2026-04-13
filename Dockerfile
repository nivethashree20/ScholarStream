# Use the official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies for PostgreSQL and PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_pgsql pdo_sqlite pdo_mysql

# Enable Apache mod_rewrite for .htaccess support
RUN a2enmod rewrite

# Set the working directory to the web root
WORKDIR /var/www/html

# Copy all project files into the container
COPY . .

# Set permissions for folders that require writing (uploads and SQLite DB)
# Note: On Render, standard file writes are ephemeral without a Disk.
RUN chown -R www-data:www-data /var/www/html/backend/uploads \
    && chmod -R 777 /var/www/html/backend/uploads \
    && chown -R www-data:www-data /var/www/html/database \
    && chmod -R 777 /var/www/html/database

# Expose port 80
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
