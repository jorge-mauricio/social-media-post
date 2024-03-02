# Use the official WordPress image as the base
# FROM wordpress:latest
FROM wordpress:6.2.2-apache

# Install additional PHP extensions if needed
RUN docker-php-ext-install pdo pdo_mysql

# Add any other custom setup or configurations here