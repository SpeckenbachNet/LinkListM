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
 * Joomla installs new plugins disabled. Each plugin of the package is switched on when this
 * run installs it for the first time (new install, or an update that adds the plugin).
 * Plugins that were there before keep the admin's choice.
 */
return new class () implements InstallerScriptInterface {
    /** group => element of the package's plugins */
    private const PLUGINS = [
        'content'     => 'linklistm',
        'editors-xtd' => 'linklistm',
    ];

    /**
     * Plugins (group) not installed before this run - checked in preflight, before the children are installed.
     *
     * @var string[]
     */
    private array $newPlugins = [];

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
        if ($type === 'uninstall') {
            return true;
        }

        foreach (self::PLUGINS as $group => $element) {
            if ($this->pluginId($group, $element) === 0) {
                $this->newPlugins[] = $group;
            }
        }

        return true;
    }

    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        if ($type === 'uninstall') {
            return true;
        }

        foreach ($this->newPlugins as $group) {
            try {
                $db    = Factory::getContainer()->get(DatabaseInterface::class);
                $query = $db->createQuery()
                    ->update($db->quoteName('#__extensions'))
                    ->set($db->quoteName('enabled') . ' = 1')
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote($group))
                    ->where($db->quoteName('element') . ' = ' . $db->quote(self::PLUGINS[$group]));

                $db->setQuery($query)->execute();
            } catch (\Throwable $e) {
                Log::add('LinkListM: plugin ' . $group . ' not enabled: ' . $e->getMessage(), Log::WARNING, 'jerror');
            }
        }

        return true;
    }

    /**
     * Extension id of the plugin, 0 if not installed. On a database error -1: then the plugin is left alone.
     */
    private function pluginId(string $group, string $element): int
    {
        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->select($db->quoteName('extension_id'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote($group))
                ->where($db->quoteName('element') . ' = ' . $db->quote($element));

            return (int) $db->setQuery($query)->loadResult();
        } catch (\Throwable) {
            return -1;
        }
    }
};
