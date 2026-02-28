FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    nginx \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql gd zip

# Set PHP upload limit
RUN echo "upload_max_filesize=10M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size=10M" >> /usr/local/etc/php/conf.d/uploads.ini

# Configure Nginx
COPY nginx.conf /etc/nginx/http.d/default.conf

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Adjust permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

# Start Nginx and PHP-FPM
CMD ["sh", "-c", "nginx && php-fpm"]
