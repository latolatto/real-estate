FROM php:8.2-apache

# Disable conflicting MPMs, keep prefork
RUN a2dismod mpm_event || true
RUN a2dismod mpm_worker || true
RUN a2enmod mpm_prefork

# Install mysqli
RUN docker-php-ext-install mysqli

# Enable rewrite
RUN a2enmod rewrite

# Copy files
COPY . /var/www/html/
