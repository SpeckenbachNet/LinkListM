<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_linklistm
 */

namespace SpeckenbachNet\Component\LinkListM\Administrator\Service;

defined('_JEXEC') or die;

/**
 * Picks the changelog.json entries to show after an install or update.
 *
 * "changes" of an entry is either a plain list or one list per language tag
 * ({"en-GB": [...], "de-DE": [...]}); a missing language falls back to English.
 */
final class ChangelogEntries
{
    public const FALLBACK = 'en-GB';

    /**
     * The changes of one entry in the given language.
     *
     * @return  string[]
     */
    public static function changes(array $entry, string $tag): array
    {
        $changes = (array) ($entry['changes'] ?? []);

        if (array_is_list($changes)) {
            return array_values(array_map('strval', $changes));
        }

        $list = $changes[$tag] ?? $changes[self::FALLBACK] ?? reset($changes) ?: [];

        return array_values(array_map('strval', (array) $list));
    }

    /**
     * All entries with their changes in one language - for the overview.
     *
     * @return  array<int, array{version: string, date: string, changes: string[]}>  as stored (newest first)
     */
    public static function localized(array $entries, string $tag): array
    {
        $result = [];

        foreach ($entries as $entry) {
            if (!is_array($entry) || (string) ($entry['version'] ?? '') === '') {
                continue;
            }

            $result[] = ['version' => (string) $entry['version'], 'date' => (string) ($entry['date'] ?? ''), 'changes' => self::changes($entry, $tag)] + $entry;
        }

        return $result;
    }

    /**
     * @param   array<int, array{version?: string, date?: string, changes?: string[]}>  $entries  content of changelog.json
     * @param   string|null  $from  version before the update; null for a new install
     * @param   string       $to    version just installed
     * @param   string       $tag   language of the changes, e.g. de-DE
     *
     * @return  array<int, array{version: string, date: string, changes: string[]}>  newest first
     */
    public static function between(array $entries, ?string $from, string $to, string $tag = self::FALLBACK): array
    {
        $selected = [];

        foreach ($entries as $entry) {
            $version = (string) ($entry['version'] ?? '');

            if ($version === '') {
                continue;
            }

            // New install: the current version only. Update: every version after the old one.
            $wanted = $from === null || $from === ''
                ? version_compare($version, $to, '==')
                : version_compare($version, $from, '>') && version_compare($version, $to, '<=');

            if ($wanted) {
                $selected[] = [
                    'version' => $version,
                    'date'    => (string) ($entry['date'] ?? ''),
                    'changes' => self::changes($entry, $tag),
                ];
            }
        }

        usort($selected, static fn (array $a, array $b): int => version_compare($b['version'], $a['version']));

        return $selected;
    }
}
