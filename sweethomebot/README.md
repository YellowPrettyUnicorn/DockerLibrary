# Docker image for SweetHomeBot

This is the docker image for SweetHomeBot on Raspberry Pi.

## How to build the image
    docker build --tag erdnussflips/sweethomebot ./build

## How to run the container
    docker volume create sweethomebot_data
    docker run -it -d -p 8070:80 \
        -v sweethomebot_data:/app/sweethomebot \
        --name sweethomebot \
        erdnussflips/sweethomebot

## Hints
You can mount the folder /app/sweethomebot as docker volume, so your data is saved across container lifecycle.

## Known limitations
- For Wake on lan to work, you must run the container on the host network stack with "--network=host"