#!/bin/bash
#
# Builds pkg_linklistm.zip (component + content plugin) and the release files in build/dist/linklistm/.
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD="$ROOT/build"
PKG_MANIFEST="$ROOT/pkg_linklistm/pkg_linklistm.xml"
COM_MANIFEST="$ROOT/com_linklistm/linklistm.xml"
PLG_MANIFEST="$ROOT/plg_content_linklistm/linklistm.xml"
EXCLUDES=(-x "*.DS_Store" -x "__MACOSX/*")

version() { sed -n 's:.*<version>\(.*\)</version>.*:\1:p' "$1" | head -1; }
VERSION="$(version "$PKG_MANIFEST")"

# Package, component and plugin share one version number
for m in "$COM_MANIFEST" "$PLG_MANIFEST"; do
    if [ "$(version "$m")" != "$VERSION" ]; then
        echo "Version mismatch: $m has $(version "$m"), package has $VERSION" >&2
        exit 1
    fi
done

# Every version needs its own SQL update file (Joomla's #__schemas tracking)
if [ ! -f "$ROOT/com_linklistm/administrator/components/com_linklistm/sql/updates/mysql/$VERSION.sql" ]; then
    echo "Missing sql/updates/mysql/$VERSION.sql" >&2
    exit 1
fi

echo "==> Syncing the component manifest into the admin folder (Joomla's database check"
echo "    needs it there without 'com_' prefix)"
cp "$COM_MANIFEST" "$ROOT/com_linklistm/administrator/components/com_linklistm/linklistm.xml"

TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
mkdir -p "$TMP/packages"

echo "==> Building com_linklistm.zip"
(cd "$ROOT/com_linklistm" && zip -r -q -X "$TMP/packages/com_linklistm.zip" linklistm.xml script.php administrator components language media "${EXCLUDES[@]}")

echo "==> Building plg_content_linklistm.zip"
(cd "$ROOT/plg_content_linklistm" && zip -r -q -X "$TMP/packages/plg_content_linklistm.zip" . "${EXCLUDES[@]}")

echo "==> Building pkg_linklistm.zip ($VERSION)"
cp "$PKG_MANIFEST" "$ROOT/pkg_linklistm/script.php" "$TMP/"
cp -R "$ROOT/pkg_linklistm/language" "$TMP/"
rm -f "$BUILD/pkg_linklistm.zip"
(cd "$TMP" && zip -r -q -X "$BUILD/pkg_linklistm.zip" pkg_linklistm.xml script.php language packages "${EXCLUDES[@]}")

php "$BUILD/make-update.php" "$BUILD/pkg_linklistm.zip"
