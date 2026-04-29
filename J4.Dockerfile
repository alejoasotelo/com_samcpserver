FROM joomla:4.3.1

# Install and enable xdebug
RUN pecl install xdebug-3.0.4 \
    && docker-php-ext-enable xdebug

# Copy xdebug configuration for remote debugging
COPY ./config-files/j4-php.ini /usr/local/etc/php/conf.d/z-php.ini