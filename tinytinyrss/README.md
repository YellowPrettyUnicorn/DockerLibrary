# Docker image for Tiny Tiny RSS

This docker image is a customized version of [universalism/docker-tt-rss-arm7](https://github.com/universalism/docker-tt-rss-arm7). Many thanks to universalism for his/her work.

## License notice
This image is not published under the LGPLv3 of this repository!

## Description
Tiny Tiny RSS docker image for all platforms supported by Debian using PHP7 and postgreSQL.

Tiny Tiny RSS version : rolling release model based on git master branch

Preinstalled Tiny Tiny RSS theme: feedly theme by [levito](https://github.com/levito/tt-rss-feedly-theme)

Official Homepage: https://tt-rss.org/

Official Git: https://git.tt-rss.org/git/tt-rss

## Start up a new PostgreSQL database container (only for ARMv7 platform):
    docker volume create ttrssdb_data
    docker run -d -it \
        -v ttrssdb_data:/var/lib/postgresql/9.5/main
        --name ttrssdb \
        universalism/docker-postgre-arm7:latest

## Start up the Tiny Tiny RSS container:
    docker run -d -it -p 8090:80 \
        --link ttrssdb:db \
        --name ttrss \
        erdnussflips/tinytinyrss

## Tiny Tiny RSS webinterface
http://localhost:8090/

Default credentials: admin / password

## Debug
To debug attach a bash:
    docker exec -it ttrss bash
