<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_linklistm
 */

namespace SpeckenbachNet\Component\LinkListM\Administrator\Service;

defined('_JEXEC') or die;

/**
 * Evaluates what Joomla's last update check found for this component.
 *
 * Kept free of CMS dependencies so it can be unit tested.
 */
final class UpdateStatus
{
    /** No update site registered (removed, or installed without the manifest entry). */
    public const NO_SERVER = 'none';

    /** Update site switched off under System → Update Sites. */
    public const DISABLED = 'disabled';

    /** Joomla has not checked the enabled update site yet. */
    public const NEVER_CHECKED = 'never';

    /** No update found, but the update site does not answer with HTTP 200 (see withProbe()). */
    public const UNREACHABLE = 'unreachable';

    /**
     * No update found at the last check. Joomla also records a check when the server was
     * unreachable (only a log entry and a one-time warning), so this does not prove reachability.
     */
    public const CURRENT   = 'current';
    public const AVAILABLE = 'available';
    public const SECURITY  = 'security';

    /**
     * @param   bool[]  $sitesEnabled  enabled flag of each update site linked to the component
     * @param   array<int, array{version: string, security: int|null}>  $updates  rows from #__updates
     * @param   int     $lastCheck     newest last_check_timestamp of the enabled update sites (0 = never)
     *
     * @return  array{state: string, version: string, security: int, checked: int}
     *          version/security = newest update; security = highest level of all pending updates (0-4)
     */
    public static function evaluate(string $installed, array $sitesEnabled, array $updates, int $lastCheck = 0): array
    {
        $result = ['state' => self::CURRENT, 'version' => '', 'security' => 0, 'checked' => max(0, $lastCheck)];

        if ($sitesEnabled === []) {
            $result['state'] = self::NO_SERVER;

            return $result;
        }

        if (!\in_array(true, $sitesEnabled, true)) {
            $result['state'] = self::DISABLED;

            return $result;
        }

        foreach ($updates as $update) {
            $version = (string) $update['version'];

            // Joomla removes the row after updating, but a stale one must not count.
            if ($version === '' || version_compare($version, $installed, '<=')) {
                continue;
            }

            if ($result['version'] === '' || version_compare($version, $result['version'], '>')) {
                $result['version'] = $version;
            }

            $result['security'] = max($result['security'], min(4, (int) ($update['security'] ?? 0)));
        }

        if ($result['version'] !== '') {
            $result['state'] = $result['security'] > 0 ? self::SECURITY : self::AVAILABLE;
        } elseif ($result['checked'] === 0) {
            $result['state'] = self::NEVER_CHECKED;
        }

        return $result;
    }

    /**
     * Joomla's check also counts when the server did not answer. With "no update found" the
     * dashboard fetches the update site itself; anything but HTTP 200 means unreachable.
     *
     * @param   array     $result    result of evaluate()
     * @param   int|null  $httpCode  status of the own request, 0 = no response, null = not probed
     *
     * @return  array  same shape plus 'http' (int|null)
     */
    public static function withProbe(array $result, ?int $httpCode): array
    {
        $result['http'] = $httpCode;

        if ($result['state'] === self::CURRENT && $httpCode !== null && $httpCode !== 200) {
            $result['state'] = self::UNREACHABLE;
        }

        return $result;
    }
}
