#!/bin/bash
#
# Builds com_linklistm.zip and the update server files in build/dist/linklistm/.
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUTPUT="$ROOT/build/com_linklistm.zip"
VERSION="$(sed -n 's:.*<version>\(.*\)</version>.*:\1:p' "$ROOT/linklistm.xml" | head -1)"

echo "==> Syncing administrator/components/com_linklistm/linklistm.xml (Joomla's database"
echo "    check needs the manifest without 'com_' prefix in the admin folder)"
cp "$ROOT/linklistm.xml" "$ROOT/administrator/components/com_linklistm/linklistm.xml"

# Every version needs its own SQL update file (Joomla's #__schemas tracking)
if [ ! -f "$ROOT/administrator/components/com_linklistm/sql/updates/mysql/$VERSION.sql" ]; then
    echo "Missing sql/updates/mysql/$VERSION.sql" >&2
    exit 1
fi

echo "==> Building com_linklistm.zip ($VERSION)"
rm -f "$OUTPUT"
(
    cd "$ROOT"
    zip -r -q -X "$OUTPUT" linklistm.xml script.php administrator components language media -x "*.DS_Store" -x "__MACOSX/*"
)

php "$ROOT/build/make-update.php" "$OUTPUT"
