#!/usr/bin/env bash

GIT_DIR=$(git rev-parse --git-dir)

if test -L "$GIT_DIR/hooks/pre-commit" ; then
    echo "Hook already exists."
    exit 0
fi
echo "Installing hooks..."
# this command creates symlink to our pre-commit script
abspath=$(cd ${0%/*} && echo $PWD/${0##*/})
ln -s $(dirname $abspath)/pre-commit.sh $GIT_DIR/hooks/pre-commit
echo "Done"!
