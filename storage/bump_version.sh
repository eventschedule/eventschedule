#!/bin/bash

current_version=$(grep "tag_name" .github/workflows/build.yml | grep -o "v1.0.[0-9]*" | cut -d "." -f3)
new_version=$((current_version+1))
date_today=$(date +%F)

echo "Bump version... $current_version => $new_version"

sed -i -e "s/tag_name: \"v1.0.$current_version\"/tag_name: \"v1.0.$new_version\"/g" .github/workflows/build.yml
sed -i -e "s/'version_installed' => env('SELF_UPDATER_VERSION_INSTALLED', 'v1.0.$current_version')/'version_installed' => env('SELF_UPDATER_VERSION_INSTALLED', 'v1.0.$new_version')/g" config/self-update.php

rm .github/workflows/build.yml-e
rm config/self-update.php-e