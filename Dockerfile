FROM php:8.2-apache

# Instalar extensión mysqli
RUN docker-php-ext-install mysqli

# Habilitar mod_rewrite 
RUN a2enmod rewrite
