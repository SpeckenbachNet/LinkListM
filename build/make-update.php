<?php

/**
 * Writes the files for the update server into build/dist/linklistm/:
 *
 *   updates.xml                  polled by Joomla (System → Updates)
 *   changelog.xml                shown in Joomla's update / extension manager
 *   com_linklistm-<ver>.zip     copy of the package, one file per version
 *
 * Usage: php build/make-update.php <path to com_linklistm.zip>
 *
 * Version and date come from the manifest, the change notes from
 * administrator/changelog.json. An entry there may carry "security": 1-4
 * (low, medium, high, critical); Joomla 6.2+ then flags the update as a
 * security release.
 */

const BASE_URL     = 'https://updates.speckenbach.net/linklistm/';
const ELEMENT      = 'com_linklistm';
const DISPLAY_NAME = 'LinkListM';
const PHP_MINIMUM  = '8.3';

$root = dirname(__DIR__);
$zip  = $argv[1] ?? '';

if (!is_file($zip)) {
    fwrite(STDERR, "Package not found: $zip\n");
    exit(1);
}

$manifest = simplexml_load_file($root . '/linklistm.xml');
$version  = (string) $manifest->version;
$entries  = json_decode((string) file_get_contents($root . '/administrator/components/com_linklistm/changelog.json'), true);

if (!is_array($entries)) {
    fwrite(STDERR, "administrator/changelog.json is not valid JSON\n");
    exit(1);
}

$current = null;

foreach ($entries as $entry) {
    if (($entry['version'] ?? '') === $version) {
        $current = $entry;
    }
}

if ($current === null) {
    fwrite(STDERR, "No changelog.json entry for version $version\n");
    exit(1);
}

// Both languages: the install message follows the backend language.
foreach (['en-GB', 'de-DE'] as $tag) {
    if (empty($current['changes'][$tag])) {
        fwrite(STDERR, "changelog.json: version $version has no changes in $tag\n");
        exit(1);
    }
}

$e        = static fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
$security = max(0, min(4, (int) ($current['security'] ?? 0)));
$zipName  = ELEMENT . '-' . $version . '.zip';
$dist     = $root . '/build/dist/linklistm';

if (!is_dir($dist) && !mkdir($dist, 0777, true)) {
    fwrite(STDERR, "Cannot create $dist\n");
    exit(1);
}

copy($zip, $dist . '/' . $zipName);

// Only the current version: Joomla offers the newest matching entry anyway, and
// older entries would point to packages that may never have been uploaded.
$updates = '<?xml version="1.0" encoding="utf-8"?>' . "\n"
    . "<updates>\n"
    . "    <update>\n"
    . '        <name>' . $e(DISPLAY_NAME) . "</name>\n"
    . '        <description>' . $e(DISPLAY_NAME . ' ' . $version) . "</description>\n"
    . '        <element>' . ELEMENT . "</element>\n"
    . "        <type>component</type>\n"
    . "        <client>administrator</client>\n"
    . '        <version>' . $e($version) . "</version>\n"
    . '        <infourl title="' . $e(DISPLAY_NAME) . '">' . $e((string) $manifest->authorUrl) . "</infourl>\n"
    . '        <changelogurl>' . BASE_URL . "changelog.xml</changelogurl>\n"
    . "        <downloads>\n"
    . '            <downloadurl type="full" format="zip">' . BASE_URL . $e($zipName) . "</downloadurl>\n"
    . "        </downloads>\n"
    . '        <sha512>' . hash_file('sha512', $zip) . "</sha512>\n"
    . ($security > 0 ? '        <security>' . $security . "</security>\n" : '')
    . "        <tags>\n"
    . "            <tag>stable</tag>\n"
    . "        </tags>\n"
    . '        <maintainer>' . $e((string) $manifest->author) . "</maintainer>\n"
    . '        <maintainerurl>' . $e((string) $manifest->authorUrl) . "</maintainerurl>\n"
    . '        <targetplatform name="joomla" version="6.*"/>' . "\n"
    . '        <php_minimum>' . PHP_MINIMUM . "</php_minimum>\n"
    . "    </update>\n"
    . "</updates>\n";

// All versions: Joomla picks the block matching the version it shows.
// Security entries go to Joomla's "Security Fixes" section, everything else to "Changes".
$changelog = '<?xml version="1.0" encoding="utf-8"?>' . "\n" . "<changelogs>\n";

foreach ($entries as $entry) {
    $section = (int) ($entry['security'] ?? 0) > 0 ? 'security' : 'change';

    $changelog .= "    <changelog>\n"
        . '        <element>' . ELEMENT . "</element>\n"
        . "        <type>component</type>\n"
        . '        <version>' . $e((string) $entry['version']) . "</version>\n"
        . "        <$section>\n";

    // Joomla's changelog has no languages: English.
    $changes = (array) ($entry['changes'] ?? []);
    $changes = array_is_list($changes) ? $changes : (array) ($changes['en-GB'] ?? []);

    foreach ($changes as $change) {
        // CDATA: expat delivers plain text in several chunks (e.g. at the first umlaut),
        // and Joomla's changelog parser turns every chunk into a bullet of its own.
        $changelog .= '            <item><![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', (string) $change) . "]]></item>\n";
    }

    $changelog .= "        </$section>\n" . "    </changelog>\n";
}

$changelog .= "</changelogs>\n";

file_put_contents($dist . '/updates.xml', $updates);
file_put_contents($dist . '/changelog.xml', $changelog);

echo "==> Update server files for $version" . ($security > 0 ? " (security level $security)" : '') . " in build/dist/linklistm/:\n";

foreach (['updates.xml', 'changelog.xml', $zipName] as $file) {
    echo "    $file\n";
}

echo "    Upload all three to " . BASE_URL . " to offer this version as an update.\n";
