#!/bin/bash
# Builds the installable component zip into build/dist/
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
VERSION="$(sed -n 's:.*<version>\(.*\)</version>.*:\1:p' "$ROOT/linklistm.xml" | head -1)"
DIST="$ROOT/build/dist"
ZIP="$DIST/com_linklistm-$VERSION.zip"

# Joomla's database check needs a manifest copy without "com_" prefix in the admin folder
cp "$ROOT/linklistm.xml" "$ROOT/administrator/components/com_linklistm/linklistm.xml"

# Every version needs its own SQL update file
if [ ! -f "$ROOT/administrator/components/com_linklistm/sql/updates/mysql/$VERSION.sql" ]; then
    echo "Missing sql/updates/mysql/$VERSION.sql" >&2
    exit 1
fi

mkdir -p "$DIST"
rm -f "$ZIP"
cd "$ROOT"
zip -rq "$ZIP" linklistm.xml administrator components language media -x '*.DS_Store'
echo "$ZIP"
