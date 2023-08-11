# SweetHomeBot
#
# VERSION 1.0

FROM resin/rpi-raspbian:stretch

LABEL maintainer="erdnussflips"

RUN apt-get update && apt-get install --no-install-recommends --no-install-suggests -y \
		nano \
		git \
		wget \
		sudo \
		cron \
		apache2 \
		ca-certificates \
		php php-sqlite3 php-mcrypt php-mbstring php-zip \
		sqlite3 \
		python3 python3-pip python3-setuptools python3-dev python3-psycopg2 \
		build-essential \
	&& pip3 install honcho pycrypto

ENV TZ Europe/Berlin
ENV APP_FOLDER /app
ENV APP_FOLDER_WIRINGPI $APP_FOLDER/wiringpi
ENV APP_FOLDER_SWEETHOMEBOT $APP_FOLDER/sweethomebot

WORKDIR ${APP_FOLDER_WIRINGPI}

RUN git clone git://git.drogon.net/wiringPi . \
	&& ./build \
	&& pip3 install wiringpi

WORKDIR ${APP_FOLDER_SWEETHOMEBOT}

RUN wget https://sweethomebot.de/data/sweethomebot_server.tar.gz -P ${APP_FOLDER_SWEETHOMEBOT}/ \
	&& tar -xzf ${APP_FOLDER_SWEETHOMEBOT}/sweethomebot_server.tar.gz -C ${APP_FOLDER_SWEETHOMEBOT}/ \
	&& rm -f ${APP_FOLDER_SWEETHOMEBOT}/sweethomebot_server.tar.gz

ADD 000-sweethomebot.conf /etc/apache2/sites-available/000-sweethomebot.conf

RUN a2dissite 000-default \
	&& a2ensite 000-sweethomebot \
# Creating certificate for 50 years and enable SSL
	&& mkdir /etc/apache2/ssl \
	&& openssl req -new -x509 -days 18275 -nodes \
		-subj '/C=DE/ST=Berlin/L=Berlin/O=SweetHomeBot/OU= /CN= ' \
		-out /etc/apache2/ssl/apache.pem \
		-keyout /etc/apache2/ssl/apache.key \
	&& chown www-data:www-data /etc/apache2/ssl/apache* \
	&& chmod 600 /etc/apache2/ssl/apache* \
# chmod 600 == chmod u=rw
	&& a2enmod ssl \
# Enable PHP modules
	&& phpenmod mcrypt mbstring zip \
# Fixing user permissions
	&& echo "www-data ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers

WORKDIR ${APP_FOLDER_SWEETHOMEBOT}/www

RUN ln -s "${APP_FOLDER_SWEETHOMEBOT}/system/shb.cron" "/etc/cron.d/sweethomebot" \
	&& php ./install.php \
# Fixing sweethomebot permissions
# chmod 700 == chmod u=rwx && chmod go=r
	&& chown -R www-data:www-data ${APP_FOLDER_SWEETHOMEBOT} \
	&& chown root:root "${APP_FOLDER_SWEETHOMEBOT}/system/shb.cron" \
	&& chmod 700 "${APP_FOLDER_SWEETHOMEBOT}/system/shb.cron"

WORKDIR ${APP_FOLDER}

ADD Procfile ${APP_FOLDER}/Procfile

VOLUME ${APP_FOLDER_SWEETHOMEBOT}

EXPOSE 80
EXPOSE 443

STOPSIGNAL SIGTERM

CMD ["honcho", "-d", "/app", "start"]
