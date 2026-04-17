# --- Stage 1: Build React Frontend ---
FROM node:20-slim AS frontend-build
WORKDIR /app
COPY frontend-react/package*.json ./
RUN npm install
COPY frontend-react/ ./
RUN npm run build

# --- Stage 2: Production PHP/Apache Server ---
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

# Copy built frontend assets from Stage 1 into the root
# Note: We copy the contents of dist/ so they are served at /
COPY --from=frontend-build /app/dist/ ./

# Set permissions for folders that require writing
RUN mkdir -p backend/uploads database && \
    chown -R www-data:www-data /var/www/html/backend/uploads /var/www/html/database && \
    chmod -R 775 /var/www/html/backend/uploads /var/www/html/database

# Expose port 80
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
