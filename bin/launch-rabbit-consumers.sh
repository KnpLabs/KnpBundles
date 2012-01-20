#!/bin/bash
TEXT="app/console rabbitmq:consumer update_bundle"

NB_TASKS=4
NB_LAUNCHED=$(ps x|grep "$TEXT"|grep -v grep|wc -l)
TASK="/usr/bin/php /site/app/console rabbitmq:consumer update_bundle --env=prod --messages=50"

for (( i=${NB_LAUNCHED}; i<${NB_TASKS}; i++ ))
do
    echo "Launching a new consumer"
    nohup $TASK &
done
