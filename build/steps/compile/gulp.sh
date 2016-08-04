#!/bin/sh
####################################
#
# Execute gulp targets to prepare static assets
#
####################################

# exit on error
set -e

# force compile on first build
test -e compiled/drupal/themes/custom/een/scss || forceCompile=true

# check for changes stripping out 'Common subdirectories' string
csdTemplatesChanges=`diff --exclude="*.git*" -r drupal/themes/custom/een/scss compiled/drupal/themes/custom/een/scss | grep "Common subdirectories" -v || true`

# check if diff is empty (no changes)
if [ ! -z "$csdTemplatesChanges" ] || [ ! -z "$forceCompile" ];then
    echo "drupal/themes/custom/een/scss has changed:"
    echo $csdTemplatesChanges
    echo "running gulp"

# TODO Add gulp to compile process
#    cd drupal
#    gulp deploy --verbose
#    cd ../../../../../

else
    echo "drupal/themes/custom/een/scss has not changed, not running gulp"
fi

