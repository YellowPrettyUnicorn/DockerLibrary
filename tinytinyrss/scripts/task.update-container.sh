#!/bin/bash
set -e
set -x

SCRIPT_PATH=$(dirname "$0")
BACKUP_TIMESTAMP=$(date +%Y-%m-%d_%H-%M-%S)

pushd ${SCRIPT_PATH}
    bash ./task.build-application.sh
    docker stop ttrssapp
    docker rename ttrssapp "ttrssapp-backup-${BACKUP_TIMESTAMP}"
    bash ./task.run.sh
popd
