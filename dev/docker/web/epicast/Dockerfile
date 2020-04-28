# start with the `delphi_web` image
FROM delphi_web

# enable mod_rewrite, aka RewriteEngine
RUN a2enmod rewrite

# enable mod_headers
RUN a2enmod headers

# create an empty htpasswd file
RUN touch /var/www/passwords

# deploy the Epicast website (see `www-epicast/deploy.json`)
# deploy files in an order that tries to take advantage of build caching

COPY repos/delphi/www-epicast/site/images/* /var/www/html/images/
COPY repos/delphi/www-epicast/site/images/flags/* /var/www/html/images/flags/
COPY repos/delphi/www-epicast/site/data/* /var/www/html/data/

COPY repos/delphi/www-epicast/site/css/* /var/www/html/css/
COPY repos/delphi/www-epicast/site/js/*.js /var/www/html/js/
COPY repos/delphi/www-epicast/site/js/us-map /var/www/html/js/us-map
COPY repos/delphi/www-epicast/site/common/* /var/www/html/common/
COPY repos/delphi/www-epicast/site/*.php /var/www/html/

# point to the local development database (overwrites `common/settings.php`)
COPY repos/delphi/www-epicast/dev/docker/web/epicast/assets/settings.php /var/www/html/common/
