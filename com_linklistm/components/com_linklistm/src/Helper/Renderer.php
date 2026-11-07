<?php

namespace SpeckenbachNet\Component\LinkListM\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Uri\Uri;
use SpeckenbachNet\Component\LinkListM\Administrator\Helper\UrlHelper;
use SpeckenbachNet\Component\LinkListM\Site\Model\LinklistsModel;

/**
 * Builds and renders link lists; used by the menu item view and by plg_content_linklistm.
 */
class Renderer
{
    /**
     * Default options, the same names as the menu item parameters.
     */
    public const DEFAULTS = [
        'catids'                => [],
        'include_subcategories' => true,
        'ordering'              => 'ordering',
        'direction'             => 'ASC',
        'show_description'      => true,
        'show_domain'           => true,
        'new_window'            => true,
        'display_style'         => 'list',
        'columns'               => 2,
    ];

    /**
     * Blocks for the given options, read through the site model.
     *
     * @param   LinklistsModel  $model    a model with its state already set (view) or a fresh one (plugin)
     * @param   array|null      $options  null = use the model state as it is; array = set it from these options
     */
    public static function blocks(LinklistsModel $model, ?array $options = null): array
    {
        if ($options !== null) {
            $options = array_merge(self::DEFAULTS, $options);

            $model->setState('filter.category_ids', array_values(array_filter(array_map('intval', (array) $options['catids']))));
            $model->setState('filter.include_subcategories', (bool) $options['include_subcategories']);
            $model->setState('linklist.ordering', $options['ordering']);
            $model->setState('linklist.direction', $options['direction']);
            $model->setState('list.start', 0);
            $model->setState('list.limit', 0);
        }

        $items = $model->getItems() ?: [];

        foreach ($items as $item) {
            $item->url    = UrlHelper::normalize((string) $item->url);
            $item->domain = UrlHelper::domain($item->url);
        }

        return CategoryTree::build(
            $model->getCategories(),
            $items,
            (array) $model->getState('filter.category_ids', []),
            (bool) $model->getState('filter.include_subcategories', true)
        );
    }

    /**
     * HTML of the link list (without page heading). Loads the stylesheet and the site language.
     */
    public static function render(array $blocks, array $options): string
    {
        $options = array_merge(self::DEFAULTS, $options);
        $app     = Factory::getApplication();

        // Outside the component (content plugin) neither stylesheet nor language are loaded yet
        $app->getLanguage()->load('com_linklistm', JPATH_SITE);

        $app->getDocument()->getWebAssetManager()
            ->registerAndUseStyle('com_linklistm.site', 'com_linklistm/linklistm.css');

        $tiles   = $options['display_style'] === 'tiles';
        $columns = (int) $options['columns'] === 3 ? 3 : 2;
        $layout  = new FileLayout('blocks', JPATH_SITE . '/components/com_linklistm/layouts');

        $html = $layout->render([
            'blocks'          => $blocks,
            'showDescription' => (bool) $options['show_description'],
            'showDomain'      => (bool) $options['show_domain'],
            'newWindow'       => (bool) $options['new_window'],
            'ownHost'         => (string) Uri::getInstance()->getHost(),
            'tiles'           => $tiles,
            'columns'         => $columns,
        ]);

        return '<div class="linklistm' . ($tiles ? ' linklistm--tiles linklistm--cols-' . $columns : '') . '">' . $html . '</div>';
    }
}
