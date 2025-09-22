#!/bin/bash

VERSION_PREFIX="v1.0."
VERSION_PREFIX_REGEX="v1\\.0\."
VERSION_SUFFIX="-dfiore1230"

current_index=$(grep "automatic_release_tag" .github/workflows/build.yml | grep -o "${VERSION_PREFIX_REGEX}[0-9]*" | cut -d "." -f3)
new_index=$((current_index+1))

current_version="${VERSION_PREFIX}${current_index}${VERSION_SUFFIX}"
new_version="${VERSION_PREFIX}${new_index}${VERSION_SUFFIX}"

echo "Bump version... ${current_version} => ${new_version}"

sed -i -e "s/automatic_release_tag: \"${current_version}\"/automatic_release_tag: \"${new_version}\"/g" .github/workflows/build.yml
sed -i -e "s/'version_installed' => env('SELF_UPDATER_VERSION_INSTALLED', '${current_version}')/'version_installed' => env('SELF_UPDATER_VERSION_INSTALLED', '${new_version}')/g" config/self-update.php

rm -f .github/workflows/build.yml-e
rm -f config/self-update.php-e
