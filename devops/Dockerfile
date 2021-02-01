# start with a standard php7+apache image
# based on https://github.com/cmu-delphi/operations
FROM php:7-apache

# use PHP's recommended configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# install and enable PHP's `mysqli` extension
RUN docker-php-ext-install mysqli && \
    docker-php-ext-enable mysqli

# use delphi's timezome
RUN ln -s -f /usr/share/zoneinfo/America/New_York /etc/localtime && \
    sed -i $PHP_INI_DIR/php.ini -e 's/^;date.timezone =$/date.timezone = "America\/New_York"/'
# enable mod_rewrite, aka RewriteEngine
RUN a2enmod rewrite && a2enmod headers

COPY ./site/ /var/www/html/
# ensure files are readable at runtime
RUN chmod o+r -R /var/www/html
