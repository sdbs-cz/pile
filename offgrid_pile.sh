#!/bin/bash
set -e

function finish() {
  echo "$(date) Cleaning up..."
  kill "${SERVER_PID}" 2>/dev/null
  rm -r "${TMP_DIR}"
}
echo "$(date) Syncing database..."
rsync -v sdbs:/var/www/sdbs-pile/db.sqlite3 .
echo "$(date) Syncing /docs..."
rsync -vr --delete sdbs:/var/www/sdbs-pile/docs/ docs/

TMP_DIR="$(mktemp -d)"
OUT_DIR="${TMP_DIR}/sdbs_pile"
mkdir -p "${OUT_DIR}"
echo "$(date) Will backup into ${OUT_DIR}"

trap finish EXIT

echo "$(date) Starting local pile server"
source .venv/bin/activate
STATIC=1 python manage.py runserver 4123 &
SERVER_PID=$!

echo "$(date) Waiting for server to start up..."
while ! curl -q http://localhost:4123 2>/dev/null >&2; do
  sleep 1
done

echo "$(date) Starting mirror."
wget --mirror --convert-links --adjust-extension --page-requisites --no-parent --no-host-directories --directory-prefix="${OUT_DIR}" http://localhost:4123

echo "$(date) Mirror done, killing server."
kill "${SERVER_PID}"

echo "$(date) Compressing archive..."
7z a sdbs_pile__$(date "+%Y-%m-%d__%H%M%S").7z "${OUT_DIR}"
