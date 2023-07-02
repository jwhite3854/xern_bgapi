FROM centos:7

LABEL name="Centos 7 + PHP 7.4 LocalDevEnv"

# update yum
RUN yum -y update && yum -y clean all
RUN yum -y install yum-utils

RUN rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm; \
    rpm -Uvh http://rpms.remirepo.net/enterprise/remi-release-7.rpm; \
    yum-config-manager --enable remi-php74

# Install some must haves
RUN yum -y install wget \
	epel-release \
	git \
	vim \
    cronie

# @todo need this?
#RUN wget https://centos7.iuscommunity.org/ius-release.rpm && rpm -Uvh ius-release.rpm

RUN yum -y update

# services

RUN yum -y install httpd

RUN yum -y install php

RUN yum -y install mysql

# php modules
RUN yum -y install \
    php-curl \
    php-devel \
    php-gmp \
    php-intl \
    php-json \
    php-mbstring \
    php-mysqlnd \
    php-pecl-memcached \
	php-pecl-xdebug \
    php-xml \
    php-zip

# other
RUN yum -y install zlib zlib-devel gcc mod_ssl

RUN echo "xdebug.idekey = PHPSTORM" >> /etc/php.d/xdebug.ini &&  \
echo "xdebug.default_enable = 1" >> /etc/php.d/xdebug.ini &&  \
echo "xdebug.remote_enable = 1" >> /etc/php.d/xdebug.ini &&  \
echo "xdebug.remote_autostart = 1" >> /etc/php.d/xdebug.ini && \
echo "xdebug.remote_connect_back = 1" >> /etc/php.d/xdebug.ini && \
echo "xdebug.profiler_enable = 0" >> /etc/php.d/xdebug.ini && \
echo "xdebug.profiler_output_dir = /tmp/" >> /etc/php.d/xdebug.ini && \
echo "xdebug.remote_host = 172.21.0.1" >> /etc/php.d/xdebug.ini && \
echo "xdebug.var_display_max_data = -1" >> /etc/php.d/xdebug.ini && \
echo "xdebug.var_display_max_children = -1" >> /etc/php.d/xdebug.ini && \
echo "xdebug.var_display_max_depth = -1" >> /etc/php.d/xdebug.ini


# apache claims to not need this, but actually does with only one vhost configured
RUN mkdir /var/www/logs
COPY ./vhost.conf /etc/httpd/conf.d/0-enable-virtualhosts.conf

# symfony likes to have a timezone configured
RUN sed -i 's/;date.timezone =/date.timezone = "America\/Phoenix"/' /etc/php.ini
RUN sed -i "s/memory_limit = 128M/memory_limit = 2G/" /etc/php.ini

RUN yum install -y php-sysvsem
RUN yum install -y php-sysvmsg
RUN yum install -y php-sysvshm

EXPOSE 80

RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- --quiet
RUN mv composer.phar /usr/local/bin/composer

# Simple startup script to avoid some issues observed with container restart
ADD run-httpd.sh /run-httpd.sh
RUN chmod -v +x /run-httpd.sh

CMD ["/run-httpd.sh"]