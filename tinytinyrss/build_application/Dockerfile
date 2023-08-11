# TinyTinyRSS
#
# VERSION 1.0

FROM debian:stretch-slim

LABEL maintainer=ErdnussFlipS

RUN apt-get update \
	&& apt-get install --no-install-recommends --no-install-suggests -y \
		supervisor \
		ca-certificates \
		git \
		curl \
		nginx \
		php-fpm php-cli \
			php-curl \
			php-gd \
			php-json \
			php-mbstring \
			php-xml \
			php-pgsql \
			php-mysql \
			php-mcrypt \
	&& apt-get clean \
	&& rm -rf /var/lib/apt/lists/*

# enable the mcrypt module
RUN phpenmod mcrypt

# add ttrss as the only nginx site
COPY files/ttrss.nginx.conf /etc/nginx/sites-available/ttrss
RUN ln -s /etc/nginx/sites-available/ttrss /etc/nginx/sites-enabled/ttrss \
	&& rm /etc/nginx/sites-enabled/default

ARG TTRSS_VERSION=master

# Install ttrss and patch configuration
WORKDIR /var/www
RUN rm -rf ./* \
	&& git clone --progress --verbose https://git.tt-rss.org/fox/tt-rss.git . && git checkout $TTRSS_VERSION \
	&& chown www-data:www-data -R /var/www \
	&& cp config.php-dist config.php

# Install plugins
RUN cd ./plugins.local \
	&& git clone --progress --verbose https://github.com/DigitalDJ/tinytinyrss-fever-plugin ./fever \
	&& git clone --progress --verbose https://github.com/hrk/tt-rss-newsplus-plugin.git ./api_newsplus \
		&& mv -f ./api_newsplus/api_newsplus/* ./api_newsplus/

ARG TTRSS_FEEDLY_THEME_VERSION=master

# Install feedly theme: https://github.com/levito/tt-rss-feedly-theme
RUN git clone --progress --verbose --branch ${TTRSS_FEEDLY_THEME_VERSION} --depth 1 https://github.com/levito/tt-rss-feedly-theme.git ./feedly_theme_download \
	&& mv -f ./feedly_theme_download/* ./themes.local/ \
	&& rm -rf ./feedly_theme_download

RUN mkdir /run/php && touch /run/php/php7.0-fpm.sock

COPY app /app
COPY files/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# expose only nginx HTTP port
EXPOSE 80

# complete path to ttrss
ENV SELF_URL_PATH http://localhost

# expose default database credentials via ENV in order to ease overwriting
ENV DB_NAME ttrss
ENV DB_USER ttrss
ENV DB_PASS ttrss

# always re-configure database with current ENV when Running container, then monitor all services
CMD php /app/configure-db.php && supervisord -c /etc/supervisor/conf.d/supervisord.conf
