#!/usr/bin/env bash

echo "Running pre-commit hook"

GIT_DIR=$(git rev-parse --git-dir)

cd $GIT_DIR
cd ..

make stan

# $? stores exit value of the last command
if [ $? -ne 0 ]; then
 echo "Code must be clean before commit!"
 exit 1
fi

make test

# $? stores exit value of the last command
if [ $? -ne 0 ]; then
 echo "Tests must pass before commit!"
 exit 1
fi