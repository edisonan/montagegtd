FROM centos:7

LABEL maintainer="accacc@126.com"

ENV PHP_ROOT=/opt/remi/php73/root/usr
ENV PATH="${PHP_ROOT}/bin:${PHP_ROOT}/sbin:${PATH}"

RUN set -eux; \
    yum -y install epel-release yum-utils; \
    yum -y install https://rpms.remirepo.net/enterprise/remi-release-7.rpm; \
    yum-config-manager --enable remi-php73; \
    yum -y update; \
    yum -y install \
        php73-php-cli \
        php73-php-common \
        php73-php-fpm \
        php73-php-json \
        php73-php-mbstring \
        php73-php-mysqlnd \
        php73-php-opcache \
        php73-php-pdo \
        php73-php-process \
        php73-php-xml \
        php73-php-gd \
        php73-php-zip \
        php73-php-bcmath \
        php73-php-intl \
        php73-php-pecl-redis; \
    yum clean all; \
    rm -rf /var/cache/yum

RUN set -eux; \
    sed -ri 's|^listen = .*|listen = 9000|' /etc/opt/remi/php73/php-fpm.d/www.conf; \
    sed -ri 's|^;?clear_env = .*|clear_env = no|' /etc/opt/remi/php73/php-fpm.d/www.conf

WORKDIR /var/www/html

EXPOSE 9000

CMD ["/opt/remi/php73/root/usr/sbin/php-fpm", "--nodaemonize", "--fpm-config", "/etc/opt/remi/php73/php-fpm.conf"]
