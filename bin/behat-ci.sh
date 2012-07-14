#!/bin/bash

# ./bin/behat-ci.sh

project_dir=$(dirname $(readlink -f $0))"/.."
logs_path=${1:-"./build/logs/behat"}
reports_path=${2:-"./build/behat"}

check_and_create_dir() {
    path=$1
    echo $path
    if [[ -d "$path" ]]; then
        # Directory already exists, do nothing
        : echo "Directory already exists"
    elif [[ -e "$path" ]]; then
        echo "$path exists and is not a directory";
    else
        mkdir -p $path
    fi
}

check_and_create_dir $reports_path
check_and_create_dir $logs_path

cd $project_dir

logs_path=${logs_path##$(pwd)/}
reports_path=${reports_path##$(pwd)/}

last_exit_code=0

for feature_path in `find src/ -path '*/Features'`; do
    var=`echo $feature_path`;
    var2=${var%Bundle*};
    bundle=`echo "${var2##*/}Bundle"`
    reports_dir=$reports_path"/$bundle.html"

    echo "Running suite for $bundle";

    ./bin/behat --format=progress,junit,html --out=,$logs_path,$reports_dir "@$bundle";
    let last_exit_code=$last_exit_code+$?
done

cd -

if (( $last_exit_code >= 1 )); then
    exit 1
fi
