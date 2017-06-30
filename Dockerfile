FROM ubuntu:14.04
MAINTAINER Alexander Steshenko <as@lcf.name>
LABEL Description="BasicCRM demo application"

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update
RUN apt-get upgrade -y

RUN apt-get install openssl ca-certificates apache2  -y
RUN echo "ServerName localhost" >> /etc/apache2/conf-available/servername.conf && a2enconf servername

RUN apt-get install -y \
    php5 \
    php5-gd \
    php5-json \
    php5-mysql \
    php5-xcache \
    php5-readline

RUN apt-get install mariadb-common mariadb-server mariadb-client -y

ENV ALLOW_OVERRIDE All
ENV DATE_TIMEZONE UTC

RUN rm /var/www/html/index.html
COPY ./ /var/www/

RUN /bin/sed -i "s/short_open_tag\ \=\ Off/short_open_tag\ \=\ On/g" /etc/php5/apache2/php.ini

RUN a2enmod rewrite
RUN chown -R www-data:www-data /var/www/html
RUN cd /var/www && php composer.phar install

RUN service mysql start && \
     mysql -u root -e "CREATE SCHEMA basiccrm" && \
     mysql -u root basiccrm < /var/www/data/dump.sql && \
     service mysql stop

VOLUME /var/www
VOLUME /var/log/httpd
VOLUME /var/lib/mysql
VOLUME /var/log/mysql

RUN /bin/sed -i 's/AllowOverride\ None/AllowOverride\ All/g' /etc/apache2/apache2.conf

RUN /bin/sed -i "s/\;date\.timezone\ \=/date\.timezone\ \=\ ${DATE_TIMEZONE}/" /etc/php5/apache2/php.ini
RUN /usr/bin/mysqld_safe --timezone=${DATE_TIMEZONE}&


EXPOSE 80
EXPOSE 3306

CMD service mysql start && /usr/sbin/apachectl -DFOREGROUND -k start
