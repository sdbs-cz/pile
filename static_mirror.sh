#!/bin/bash
set -e
rsync -vr --delete sdbs:/var/www/sdbs-pile/docs/ docs/
rsync sdbs:/var/www/sdbs-pile/db.sqlite3 .
rm -rf static_pile
wget --mirror --convert-links --adjust-extension --page-requisites --no-parent --directory-prefix=static_pile http://localhost:4002
