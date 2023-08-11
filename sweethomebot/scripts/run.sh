#!/bin/bash

docker run -d -p 8070:80 -it \
	-v sweethomebot_data:/app/sweethomebot \
	--restart always \
	--name sweethomebot \
	erdnussflips/sweethomebot
