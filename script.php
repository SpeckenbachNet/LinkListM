<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_linklistm
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use SpeckenbachNet\Component\LinkListM\Administrator\Service\ChangelogEntries;

/**
 * Installation script for com_linklistm.
 */
return new class () implements InstallerScriptInterface {
    private const MINIMUM_PHP    = '8.3';
    private const MINIMUM_JOOMLA = '6.0';

    /**
     * Version installed before an update; null on a new install.
     */
    private ?string $previousVersion = null;

    public function install(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function uninstall(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP, '<')) {
            Log::add(sprintf('LinkListM requires at least PHP %s.', self::MINIMUM_PHP), Log::ERROR, 'jerror');

            return false;
        }

        if (version_compare(JVERSION, self::MINIMUM_JOOMLA, '<')) {
            Log::add(sprintf('LinkListM requires at least Joomla %s.', self::MINIMUM_JOOMLA), Log::ERROR, 'jerror');

            return false;
        }

        // The manifest cache still holds the old version at this point.
        if ($type === 'update') {
            $this->previousVersion = $this->installedVersion();
        }

        return true;
    }

    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        // Joomla only rebuilds its cached namespace map when the file is missing;
        // a stale map can cause "Class not found" errors after an update.
        $file = JPATH_CACHE . '/autoload_psr4.php';

        if (is_file($file)) {
            @unlink($file);
        }

        if (!\in_array($type, ['install', 'update', 'discover_install'], true)) {
            return true;
        }

        $changes = $this->changesHtml((string) $adapter->getManifest()->version);

        if ($changes === '') {
            return true;
        }

        if ($type === 'update') {
            // After System → Updates Joomla shows the (empty) update list, which drops
            // the script output - a system message survives the redirect.
            Factory::getApplication()->enqueueMessage($changes, 'message');
        } else {
            echo $changes;
        }

        return true;
    }

    private function installedVersion(): ?string
    {
        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->select($db->quoteName('manifest_cache'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_linklistm'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

            $cache = json_decode((string) $db->setQuery($query)->loadResult(), true);
        } catch (\Throwable) {
            return null;
        }

        return \is_array($cache) && !empty($cache['version']) ? (string) $cache['version'] : null;
    }

    /**
     * Changes of the new version (new install) or of every version since the old one (update).
     */
    private function changesHtml(string $version): string
    {
        $base = JPATH_ADMINISTRATOR . '/components/com_linklistm';
        $json = @file_get_contents($base . '/changelog.json');

        if ($json === false) {
            return '';
        }

        // The component's autoloader is not registered during installation.
        if (!class_exists(ChangelogEntries::class, false)) {
            require_once $base . '/src/Service/ChangelogEntries.php';
        }

        $tag     = Factory::getApplication()->getLanguage()->getTag();
        $entries = ChangelogEntries::between((array) json_decode($json, true), $this->previousVersion, $version, $tag);

        if ($entries === []) {
            return '';
        }

        $e    = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $html = '<div class="linklistm-changes mt-3">';
        $html .= '<p><strong>' . $e($this->previousVersion !== null
            ? Text::sprintf('COM_LINKLISTM_INSTALL_UPDATED', $this->previousVersion, $version)
            : Text::sprintf('COM_LINKLISTM_INSTALL_INSTALLED', $version)) . '</strong></p>';

        foreach ($entries as $entry) {
            $html .= '<h4 class="h6 mb-1">' . $e($entry['version'])
                . ($entry['date'] !== '' ? ' <small class="text-muted">(' . $e($entry['date']) . ')</small>' : '') . '</h4>';
            $html .= '<ul class="mb-3">';

            foreach ($entry['changes'] as $change) {
                $html .= '<li>' . $e($change) . '</li>';
            }

            $html .= '</ul>';
        }

        return $html . '</div>';
    }
};
