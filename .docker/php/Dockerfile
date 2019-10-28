FROM centos:7

RUN yum -y install \
    epel-release \
    https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm \
    http://rpms.remirepo.net/enterprise/remi-release-7.rpm

RUN yum -y update

RUN mkdir /run/php-fpm

RUN yum install --enablerepo=remi-php73 -y \
    php \
    php-fpm \
    php-xdebug \
    php-bcmath \
    php-curl \
    php-fileinfo \
    php-gd \
    php-imagick \
    php-json \
    php-ldap \
    php-mbstring \
    php-mcrypt \
    php-memcached \
    php-mysql \
    php-opcache \
    php-pdo \
    php-posix \
    php-simplexml \
    php-soap \
    php-xml \
    php-pinba \
    php-zip \
    php-calendar \
    php-intl

RUN yum install -y \
    # composer dependency
    unzip \
    git

# install composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# install *toPdf
COPY rpm/pdftk-2.02-1.el7.x86_64.rpm /tmp/pdftk-2.02-1.el7.x86_64.rpm
COPY wkhtmltool/* /usr/bin/

RUN yum install -y \
    libpng \
    libjpeg \
    openssl \
    icu \
    libX11 \
    libXext \
    libXrender \
    xorg-x11-fonts-Type1 \
    xorg-x11-fonts-75dpi \
    /tmp/pdftk-2.02-1.el7.x86_64.rpm \
    && \
    chmod 0755 /usr/bin/wkhtmltopdf-amd64 && \
    chmod 0755 /usr/bin/wkhtmltoimage && \
    chmod 0755 /usr/bin/wkhtmltopdf

## install grpc
RUN yum install --enablerepo=remi-php73 -y php-devel php-pear phpunit gcc zlib-devel gcc-c++ make
RUN pecl install grpc-1.22.0

# cleanup
RUN yum remove -y \
    gcc \
    make \
    php-devel \
    gcc-c++.x86_64 \
    phpunit \
    zlib-devel \
    gcc-c++ \
    && \
    rm -rf /tmp/*

WORKDIR /var/www/gamex.local
EXPOSE 9000
CMD php-fpm -F -R
