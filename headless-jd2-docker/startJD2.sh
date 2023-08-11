#!/bin/bash

function stopJD2 {
	PID=$(cat JDownloader.pid)
	kill $PID
	wait $PID
	exit
}

UID=${UID:-"0"}
GID=${GID:-"0"}

if [ "$GID" -ne "0" ]
then
	GROUP=jdownloader
	if ! groupadd -g $GID $GROUP ; then
		GROUP=$(getent group "${GID}" | cut -d: -f1)
	fi
else
	GROUP=root
fi

if [ "$UID" -ne "0" ]
then
	USER=jdownloader
	if ! useradd -r -N -s /bin/false -u $UID $USER ; then
		USER=$(getent passwd "${UID}" | cut -d: -f1)
	fi
else
	USER=root
fi

useradd -G $GROUP $USER
chown -R $UID:$GID /opt/JDownloader

trap stopJD2 EXIT

su -c "java -Djava.awt.headless=true -jar /opt/JDownloader/JDownloader.jar &" -s /bin/bash $USER

while true; do
	sleep inf
done

