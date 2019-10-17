FROM php:7.2-apache

COPY www/ /var/www/html/
RUN rm -f /var/www/html/pile.db
COPY opt/ /opt/pile
RUN mkdir -p /var/tmp/pile
COPY db_versions/ /var/tmp/pile/db_versions

RUN apt-get update && apt-get -y install sqlite3 wget \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev

RUN docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

RUN cd /opt/pile/ && /opt/pile/install_composer.sh
RUN cd /var/www/html && /opt/pile/composer.phar install

RUN /var/tmp/pile/db_versions/apply_all.sh /var/www/html/pile.db
