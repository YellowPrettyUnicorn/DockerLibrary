# docker-postgre-arm7
PostgreSQL server v9.5 for Raspberry Pi / arm7 / arm8

## Run

    docker run -d --name ttrssdb universalism/docker-postgre-arm7:latest

## Credentials

Login / password : docker / docker

## Project using this image

https://github.com/universalism/docker-tt-rss-arm7

## Build the image yourself

    git clone https://github.com/universalism/docker-postgre-arm7.git
    cd docker-postgre-arm7
    docker build . < Dockerfile
