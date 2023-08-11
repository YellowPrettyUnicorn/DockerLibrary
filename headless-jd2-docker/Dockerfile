FROM openjdk:8-jre

MAINTAINER PlusMinus <piddlpiddl@gmail.com>

# Install some deps
RUN \
	apt-get update \
	&& apt-get install --no-install-recommends --no-install-suggests -y \
		ffmpeg \
		phantomjs \
	&& apt-get clean \
	&& rm -rf /var/lib/apt/lists/*

# Create directory, downloader JD" and start JD2 for the initial update and creation of config files.
RUN \
	mkdir -p /opt/JDownloader/ \
	&& wget -O /opt/JDownloader/JDownloader.jar --user-agent="https://hub.docker.com/r/plusminus/jdownloader2-headless/" --progress=bar:force http://installer.jdownloader.org/JDownloader.jar \
	&& java -Djava.awt.headless=true -jar /opt/JDownloader/JDownloader.jar

COPY startJD2.sh /opt/JDownloader/
RUN chmod +x /opt/JDownloader/startJD2.sh

WORKDIR /opt/JDownloader/

# Run this when the container is started
CMD /opt/JDownloader/startJD2.sh
