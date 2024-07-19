#!/bin/bash

pushd wp-content/plugins/assets-plugin
composer install
npm install
popd
popd
