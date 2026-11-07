<?php

namespace SpeckenbachNet\Component\LinkListM\Administrator\Helper;

\defined('_JEXEC') or die;

/**
 * URL helpers shared by backend (saving) and frontend (output).
 */
class UrlHelper
{
    /**
     * Adds "https://" to URLs entered without a scheme (e.g. "www.example.com"),
     * otherwise Joomla treats them as relative links of the own site.
     */
    public static function normalize(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        // Keep URLs with a scheme (https:, mailto:, tel: ...), protocol-relative, root-relative and anchors
        if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $url) || str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return $url;
        }

        return 'https://' . $url;
    }

    /**
     * Returns the host without "www." for display, or '' if there is none.
     */
    public static function domain(string $url): string
    {
        $host = (string) parse_url(self::normalize($url), PHP_URL_HOST);

        return preg_replace('#^www\.#i', '', $host);
    }

    /**
     * True if the URL points to another site.
     */
    public static function isExternal(string $url, string $ownHost): bool
    {
        $host = (string) parse_url(self::normalize($url), PHP_URL_HOST);

        return $host !== '' && strcasecmp(preg_replace('#^www\.#i', '', $host), preg_replace('#^www\.#i', '', $ownHost)) !== 0;
    }
}
