FROM php:8.2-apache

# Install mysqli for MySQL
RUN docker-php-ext-install mysqli

# Enable Apache rewrite
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html/
