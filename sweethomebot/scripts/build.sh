#!/bin/bash

SCRIPT_FOLDER_PATH=$(realpath $(dirname "${BASH_SOURCE[0]}"))

pushd "$SCRIPT_FOLDER_PATH"/../build
docker build --tag erdnussflips/sweethomebot .
popd
