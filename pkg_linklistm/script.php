<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

/**
 * Installation script for pkg_linklistm.
 *
 * Joomla installs new plugins disabled. The content plugin is switched on when this run
 * installs it for the first time (new install, or update from a component-only version).
 * If it was there before, the admin's choice stays.
 */
return new class () implements InstallerScriptInterface {
    /**
     * Plugin already installed before this run (checked in preflight, before the children are installed).
     */
    private bool $pluginExisted = true;

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
        if ($type !== 'uninstall') {
            $this->pluginExisted = $this->pluginId() > 0;
        }

        return true;
    }

    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        if ($type === 'uninstall' || $this->pluginExisted) {
            return true;
        }

        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('content'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('linklistm'));

            $db->setQuery($query)->execute();
        } catch (\Throwable $e) {
            Log::add('LinkListM: content plugin not enabled: ' . $e->getMessage(), Log::WARNING, 'jerror');
        }

        return true;
    }

    private function pluginId(): int
    {
        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->select($db->quoteName('extension_id'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('content'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('linklistm'));

            return (int) $db->setQuery($query)->loadResult();
        } catch (\Throwable) {
            // Unknown: do not touch the plugin
            return 1;
        }
    }
};
