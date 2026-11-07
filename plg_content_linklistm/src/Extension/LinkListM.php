<?php

namespace SpeckenbachNet\Plugin\Content\LinkListM\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use SpeckenbachNet\Component\LinkListM\Site\Helper\Renderer;

/**
 * Replaces {linklistm ...} in articles, Custom modules and other prepared content
 * with a link list of com_linklistm.
 *
 * {linklistm}                        all categories
 * {linklistm cat=3,5}                these categories (with subcategories)
 * {linklistm cat=3 sub=0 style=tiles cols=3 desc=0 domain=0 order=title dir=desc newwindow=0}
 */
final class LinkListM extends CMSPlugin implements SubscriberInterface
{
    /**
     * Matches the tag, optionally wrapped in its own paragraph by the editor.
     */
    private const REGEX = '#(?:<p[^>]*>\s*)?\{linklistm(\s[^}]*)?\}(?:\s*</p>)?#i';

    public static function getSubscribedEvents(): array
    {
        return ['onContentPrepare' => 'onContentPrepare'];
    }

    public function onContentPrepare(ContentPrepareEvent $event): void
    {
        $item = $event->getItem();

        if (!\is_object($item) || !property_exists($item, 'text') || !\is_string($item->text)) {
            return;
        }

        if (stripos($item->text, '{linklistm') === false) {
            return;
        }

        // No link lists in the search index or the API: just remove the tags
        if ($event->getContext() === 'com_finder.indexer' || $this->getApplication()->isClient('api')) {
            $item->text = preg_replace(self::REGEX, '', $item->text);

            return;
        }

        if (!$this->getApplication()->isClient('site') || !ComponentHelper::isEnabled('com_linklistm')) {
            return;
        }

        // Booting the component registers its namespaces and gives access to its models
        $factory = $this->getApplication()->bootComponent('com_linklistm')->getMVCFactory();

        $item->text = preg_replace_callback(
            self::REGEX,
            function (array $match) use ($factory): string {
                $options = self::parseOptions($match[1] ?? '');
                $model   = $factory->createModel('Linklists', 'Site', ['ignore_request' => true]);

                if (!$model) {
                    return '';
                }

                $blocks = Renderer::blocks($model, $options);

                return $blocks === [] ? '' : Renderer::render($blocks, $options);
            },
            $item->text
        );
    }

    /**
     * Turns 'cat=3,5 sub=0 style=tiles' into Renderer options. Unknown keys are ignored.
     */
    public static function parseOptions(string $attributes): array
    {
        // Editors may write &nbsp; or encode quotes
        $attributes = str_replace("\u{00A0}", ' ', html_entity_decode($attributes, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $options    = [];

        preg_match_all('#(\w+)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s]+)#', $attributes, $pairs, PREG_SET_ORDER);

        foreach ($pairs as [, $key, $value]) {
            $key   = strtolower($key);
            $value = strtolower(trim($value, '"\''));
            $flag  = !\in_array($value, ['0', 'no', 'false', 'off', 'nein'], true);

            match ($key) {
                'cat', 'cats', 'catid', 'catids' => $options['catids'] = array_values(array_filter(array_map('intval', preg_split('#[,;\s]+#', $value)))),
                'sub'       => $options['include_subcategories'] = $flag,
                'style'     => $options['display_style'] = $value === 'tiles' ? 'tiles' : 'list',
                'cols'      => $options['columns'] = (int) $value === 3 ? 3 : 2,
                'desc'      => $options['show_description'] = $flag,
                'domain'    => $options['show_domain'] = $flag,
                'newwindow' => $options['new_window'] = $flag,
                'order'     => $options['ordering'] = $value === 'title' ? 'title' : 'ordering',
                'dir'       => $options['direction'] = $value === 'desc' ? 'DESC' : 'ASC',
                default     => null,
            };
        }

        return $options;
    }
}
