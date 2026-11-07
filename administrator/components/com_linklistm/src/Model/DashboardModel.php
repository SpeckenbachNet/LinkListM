<?php

namespace SpeckenbachNet\Component\LinkListM\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use SpeckenbachNet\Component\LinkListM\Administrator\Service\ChangelogEntries;
use SpeckenbachNet\Component\LinkListM\Administrator\Service\UpdateStatus;

/**
 * Component start page: what is there, what needs attention.
 */
class DashboardModel extends BaseDatabaseModel
{
    /**
     * @return  array{links: int, categories: int}
     */
    public function getTileCounts(): array
    {
        $db = $this->getDatabase();

        $links = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__linklistm_links'))
            ->whereIn($db->quoteName('state'), [0, 1]);

        $categories = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__categories'))
            ->where($db->quoteName('extension') . ' = ' . $db->quote('com_linklistm'))
            ->whereIn($db->quoteName('published'), [0, 1]);

        return [
            'links'      => (int) $db->setQuery($links)->loadResult(),
            'categories' => (int) $db->setQuery($categories)->loadResult(),
        ];
    }

    /**
     * Categories in tree order with their link counts.
     *
     * @return  object[]  id, title, level, published, lft, rgt, published_links, unpublished_links, total (incl. subcategories)
     */
    public function getCategories(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['c.id', 'c.title', 'c.level', 'c.published', 'c.lft', 'c.rgt']))
            ->select('SUM(CASE WHEN ' . $db->quoteName('a.state') . ' = 1 THEN 1 ELSE 0 END) AS ' . $db->quoteName('published_links'))
            ->select('SUM(CASE WHEN ' . $db->quoteName('a.state') . ' = 0 THEN 1 ELSE 0 END) AS ' . $db->quoteName('unpublished_links'))
            ->from($db->quoteName('#__categories', 'c'))
            ->join('LEFT', $db->quoteName('#__linklistm_links', 'a'), 'a.catid = c.id')
            ->where($db->quoteName('c.extension') . ' = ' . $db->quote('com_linklistm'))
            ->whereIn($db->quoteName('c.published'), [0, 1])
            ->group($db->quoteName(['c.id', 'c.title', 'c.level', 'c.published', 'c.lft', 'c.rgt']))
            ->order($db->quoteName('c.lft') . ' ASC');

        $categories = $db->setQuery($query)->loadObjectList();

        // Links including all subcategories (published and unpublished)
        foreach ($categories as $category) {
            $category->published_links   = (int) $category->published_links;
            $category->unpublished_links = (int) $category->unpublished_links;
            $category->total             = 0;

            foreach ($categories as $other) {
                if ((int) $other->lft >= (int) $category->lft && (int) $other->rgt <= (int) $category->rgt) {
                    $category->total += (int) $other->published_links + (int) $other->unpublished_links;
                }
            }
        }

        return $categories;
    }

    /**
     * Things that need attention.
     *
     * @param   object[]  $categories  from getCategories()
     * @param   array     $update      result of getUpdateStatus()
     *
     * @return  array<int, array{type: string, count?: int, category?: object, update?: array}>
     */
    public function getTodos(array $categories, array $update = []): array
    {
        $todos = [];

        if (($update['state'] ?? '') === UpdateStatus::SECURITY) {
            $todos[] = ['type' => 'security_update', 'update' => $update];
        }

        if ($categories === []) {
            $todos[] = ['type' => 'no_categories'];
        }

        $unpublished = array_sum(array_map(static fn ($c): int => $c->unpublished_links, $categories));

        if ($unpublished > 0) {
            $todos[] = ['type' => 'unpublished', 'count' => $unpublished];
        }

        foreach ($categories as $category) {
            if ($category->total === 0) {
                $todos[] = ['type' => 'empty_category', 'category' => $category];
            }
        }

        if ($categories !== [] && !$this->hasMenuItem()) {
            $todos[] = ['type' => 'no_menu_item'];
        }

        if (($update['state'] ?? '') === UpdateStatus::AVAILABLE) {
            $todos[] = ['type' => 'update', 'update' => $update];
        }

        return $todos;
    }

    /**
     * True if a published site menu item shows the link list.
     */
    private function hasMenuItem(): bool
    {
        $db    = $this->getDatabase();
        $link  = 'index.php?option=com_linklistm&view=linklists%';
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('client_id') . ' = 0')
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('link') . ' LIKE :link')
            ->bind(':link', $link);

        return (int) $db->setQuery($query)->loadResult() > 0;
    }

    /**
     * @return  array<int, array{ok: bool, key: string, args: array, danger?: bool, link?: string}>
     */
    public function getChecks(string $version, array $update = []): array
    {
        $db     = $this->getDatabase();
        $checks = [];

        $checks[] = ['ok' => true, 'key' => 'COM_LINKLISTM_CHECK_VERSIONS', 'args' => [JVERSION, PHP_VERSION]];

        // Database schema: newest applied update file must match the installed version
        $query = $db->getQuery(true)
            ->select($db->quoteName('s.version_id'))
            ->from($db->quoteName('#__schemas', 's'))
            ->join('INNER', $db->quoteName('#__extensions', 'e'), 'e.extension_id = s.extension_id')
            ->where($db->quoteName('e.element') . ' = ' . $db->quote('com_linklistm'))
            ->where($db->quoteName('e.type') . ' = ' . $db->quote('component'));
        $schema = (string) $db->setQuery($query)->loadResult();

        $checks[] = $schema === $version
            ? ['ok' => true, 'key' => 'COM_LINKLISTM_CHECK_DB_OK', 'args' => [$schema]]
            : ['ok' => false, 'key' => 'COM_LINKLISTM_CHECK_DB_OUTDATED', 'args' => [$schema ?: '–', $version], 'link' => 'database'];

        if ($update !== []) {
            $checks[] = match ($update['state']) {
                UpdateStatus::NO_SERVER => ['ok' => false, 'key' => 'COM_LINKLISTM_CHECK_UPDATE_NO_SERVER', 'args' => []],
                UpdateStatus::DISABLED  => ['ok' => false, 'key' => 'COM_LINKLISTM_CHECK_UPDATE_DISABLED', 'args' => [], 'link' => 'updatesites'],
                UpdateStatus::AVAILABLE => ['ok' => false, 'key' => 'COM_LINKLISTM_CHECK_UPDATE_AVAILABLE', 'args' => [$update['version']], 'link' => 'update'],
                UpdateStatus::SECURITY  => [
                    'ok'     => false,
                    'danger' => true,
                    'key'    => 'COM_LINKLISTM_CHECK_UPDATE_SECURITY',
                    'args'   => [$update['version'], Text::_('COM_LINKLISTM_SECURITY_LEVEL_' . $update['security'])],
                    'link'   => 'update',
                ],
                UpdateStatus::NEVER_CHECKED => ['ok' => false, 'key' => 'COM_LINKLISTM_CHECK_UPDATE_NEVER', 'args' => [], 'link' => 'update'],
                UpdateStatus::UNREACHABLE   => [
                    'ok'   => false,
                    'key'  => 'COM_LINKLISTM_CHECK_UPDATE_UNREACHABLE',
                    'args' => [$update['http'] ? (string) $update['http'] : Text::_('COM_LINKLISTM_CHECK_UPDATE_NO_RESPONSE')],
                    'link' => 'updatesites',
                ],
                // A check is also recorded when the server was unreachable: show when it ran
                default => [
                    'ok'   => true,
                    'key'  => 'COM_LINKLISTM_CHECK_UPDATE_CURRENT',
                    'args' => [HTMLHelper::_('date', Factory::getDate($update['checked'])->toSql(), Text::_('DATE_FORMAT_LC5'))],
                    'link' => 'update',
                ],
            };
        }

        return $checks;
    }

    /**
     * What Joomla's last update check found for this component (no request of its own).
     *
     * @return  array{state: string, version: string, security: int, checked: int}
     */
    public function getUpdateStatus(string $installed): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_linklistm'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
        $extensionId = (int) $db->setQuery($query)->loadResult();

        $query = $db->getQuery(true)
            ->select($db->quoteName(['s.enabled', 's.last_check_timestamp', 's.location']))
            ->from($db->quoteName('#__update_sites', 's'))
            ->join('INNER', $db->quoteName('#__update_sites_extensions', 'se'), 'se.update_site_id = s.update_site_id')
            ->where($db->quoteName('se.extension_id') . ' = ' . $extensionId);
        $sites        = $db->setQuery($query)->loadObjectList();
        $sitesEnabled = array_map(static fn (object $site): bool => (int) $site->enabled === 1, $sites);

        // Newest check of an enabled site (NULL = never checked)
        $lastCheck = 0;

        foreach ($sites as $site) {
            if ((int) $site->enabled === 1) {
                $lastCheck = max($lastCheck, (int) $site->last_check_timestamp);
            }
        }

        // SELECT *: the security column only exists since Joomla 6.2
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__updates'))
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId);
        $updates = array_map(
            static fn (object $row): array => ['version' => (string) $row->version, 'security' => isset($row->security) ? (int) $row->security : 0],
            $db->setQuery($query)->loadObjectList()
        );

        $result = UpdateStatus::evaluate($installed, $sitesEnabled, $updates, $lastCheck);

        // "No update found" is also recorded when the server did not answer: ask it ourselves
        if ($result['state'] === UpdateStatus::CURRENT) {
            $location = '';

            foreach ($sites as $site) {
                if ((int) $site->enabled === 1 && trim((string) $site->location) !== '') {
                    $location = trim((string) $site->location);
                    break;
                }
            }

            $result = UpdateStatus::withProbe($result, $location !== '' ? $this->probeUpdateSite($location, $lastCheck) : null);
        }

        return $result;
    }

    /**
     * HTTP status of the update site (0 = no response). Cached for 6 hours; a new check by
     * Joomla (other timestamp) probes again.
     */
    private function probeUpdateSite(string $url, int $lastCheck): int
    {
        $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController(
            'output',
            ['defaultgroup' => 'com_linklistm', 'lifetime' => 360, 'caching' => true]
        );
        $id     = md5($url . '|' . $lastCheck);
        $cached = $cache->get($id);

        if ($cached !== false) {
            return (int) $cached;
        }

        try {
            $response = (new HttpFactory())->getHttp()->get($url, [], 3);
            $code     = (int) $response->getStatusCode();
        } catch (\Throwable) {
            // Transport error, timeout or invalid status: no usable response
            $code = 0;
        }

        $cache->store($code, $id);

        return $code;
    }

    public function getInfo(): array
    {
        $file = JPATH_ADMINISTRATOR . '/components/com_linklistm/linklistm.xml';
        $xml  = is_file($file) ? simplexml_load_file($file) : false;

        return [
            'name'    => 'com_linklistm',
            'version' => $xml ? (string) $xml->version : '',
            'date'    => $xml ? (string) $xml->creationDate : '',
            'author'  => $xml ? (string) $xml->author : '',
            'email'   => $xml ? (string) $xml->authorEmail : '',
            'url'     => $xml ? (string) $xml->authorUrl : '',
            'license' => $xml ? (string) $xml->license : '',
        ];
    }

    public function getChangelog(): array
    {
        $file = JPATH_ADMINISTRATOR . '/components/com_linklistm/changelog.json';
        $data = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;

        return \is_array($data)
            ? ChangelogEntries::localized($data, Factory::getApplication()->getLanguage()->getTag())
            : [];
    }
}
