FROM php:8.2-apache

# Enable mysqli
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy backend source code
COPY . /var/www/html/

EXPOSE 80
