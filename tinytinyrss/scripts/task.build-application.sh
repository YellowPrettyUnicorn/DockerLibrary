#!/bin/bash
set -e
set -x

SCRIPT_PATH=$(dirname "$0")

TTRSS_VERSION_HASH="master"
TTRSS_FEEDLY_THEME_VERSION_HASH_TAG_BRANCH="v1.4.0"
#TTRSS_VERSION_DATE="2018-04-03"
TTRSS_VERSION_DATE=$(date +%Y-%m-%d)
IMAGE_TAG="${TTRSS_VERSION_DATE}-${TTRSS_VERSION_HASH}"

pushd ${SCRIPT_PATH}/../build_application
docker build \
    --pull \
    --build-arg "TTRSS_VERSION=${TTRSS_VERSION_HASH}" \
    --build-arg "TTRSS_FEEDLY_THEME_VERSION=${TTRSS_FEEDLY_THEME_VERSION_HASH_TAG_BRANCH}" \
    --tag "erdnussflips/tinytinyrss:${IMAGE_TAG}" \
    --tag erdnussflips/tinytinyrss:latest \
    .
popd