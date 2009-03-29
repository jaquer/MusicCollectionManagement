#!/bin/bash

# cron_update.sh - check if new symlinks need to be created
#                  should be run periodically via cron

BASE_DIR="/var/www/music"
SIGNAL_FILE="${BASE_DIR}/_cache/update_signal"
PROGRESS_FILE="${BASE_DIR}/_cache/update_in_progress"

PHP="/usr/bin/php"
SCRIPT="${BASE_DIR}/create_symlinks.php"
UPDATEDB="${BASE_DIR}/update_db.php"

if [ -w ${SIGNAL_FILE} -a ! -f ${PROGRESS_FILE} ]; then

  mv ${SIGNAL_FILE} ${PROGRESS_FILE}
  echo $$ > ${PROGRESS_FILE}

  ${PHP} ${SCRIPT}

  sleep 5
  
  rm ${PROGRESS_FILE}
  
fi

${PHP} ${UPDATEDB}
