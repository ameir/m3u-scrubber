FROM php:7.2.8-cli-alpine3.7
WORKDIR /var/tmp
COPY convert.php composer.* ./
RUN wget -O /usr/bin/composer https://getcomposer.org/download/1.7.1/composer.phar && \
    chmod 755 /usr/bin/composer && \
    composer install -o

CMD ./convert.php
