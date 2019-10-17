#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

if [ -z "$1" ]; then
	echo "Please specify database file."
	exit -1
fi

if ! which sqlite3; then
	echo "Couldn't find \`sqlite3\` in \$PATH!"
	exit -1
fi

set -e
FILES=$(ls ${DIR}/*.sql|sort)
for sql_file in $FILES;do
	echo "Processing \"${sql_file}\"..."
	sqlite3 -echo "$1" < "$sql_file"
done

echo "Done."
