#!/bin/bash

current_version=$(grep "automatic_release_tag" .github/workflows/build.yml | grep -o "v1.0.[0-9]*" | cut -d "." -f3)
new_version=$((current_version+1))
date_today=$(date +%F)

echo "Bump version... $current_version => $new_version"

sed -i -e "s/automatic_release_tag: \"v0.9.$current_version\"/automatic_release_tag: \"v0.9.$new_version\"/g" .github/workflows/build.yml
sed -i -e "s/'version_installed' => env('SELF_UPDATER_VERSION_INSTALLED', 'v0.9.$current_version')/'version_installed' => env('SELF_UPDATER_VERSION_INSTALLED', 'v0.9.$new_version')/g" config/self-update.php

rm .github/workflows/build.yml-e
rm config/self-update.php-e