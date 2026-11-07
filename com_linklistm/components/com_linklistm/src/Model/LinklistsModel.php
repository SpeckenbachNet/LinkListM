<?php

namespace SpeckenbachNet\Component\LinkListM\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

class LinklistsModel extends ListModel
{
    /**
     * Cached list of published, accessible categories.
     *
     * @var object[]|null
     */
    protected $categories = null;

    /**
     * Returns all published categories of com_linklistm the current user may see, ordered by tree.
     * Parent chain checks are done in CategoryTree.
     */
    public function getCategories(): array
    {
        if ($this->categories !== null) {
            return $this->categories;
        }

        $db    = $this->getDatabase();
        $user  = $this->getCurrentUser();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'parent_id', 'title', 'level', 'lft', 'rgt']))
            ->from($db->quoteName('#__categories'))
            ->where($db->quoteName('extension') . ' = ' . $db->quote('com_linklistm'))
            ->where($db->quoteName('published') . ' = 1')
            ->whereIn($db->quoteName('access'), $user->getAuthorisedViewLevels())
            ->order($db->quoteName('lft') . ' ASC');

        if (Multilanguage::isEnabled()) {
            $query->whereIn(
                $db->quoteName('language'),
                [Factory::getApplication()->getLanguage()->getTag(), '*'],
                ParameterType::STRING
            );
        }

        $this->categories = $db->setQuery($query)->loadObjectList();

        return $this->categories;
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $user  = $this->getCurrentUser();
        $now   = Factory::getDate()->toSql();
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['a.id', 'a.title', 'a.url', 'a.description', 'a.catid']))
            ->from($db->quoteName('#__linklistm_links', 'a'))
            ->where($db->quoteName('a.state') . ' = 1')
            ->whereIn($db->quoteName('a.access'), $user->getAuthorisedViewLevels())
            ->where('(' . $db->quoteName('a.publish_up') . ' IS NULL OR ' . $db->quoteName('a.publish_up') . ' <= :now1)')
            ->where('(' . $db->quoteName('a.publish_down') . ' IS NULL OR ' . $db->quoteName('a.publish_down') . ' > :now2)')
            ->bind(':now1', $now)
            ->bind(':now2', $now);

        if (Multilanguage::isEnabled()) {
            $query->whereIn(
                $db->quoteName('a.language'),
                [Factory::getApplication()->getLanguage()->getTag(), '*'],
                ParameterType::STRING
            );
        }

        // Only links in visible categories; selection by menu item is done in CategoryTree
        $catIds = array_map(fn($c) => (int) $c->id, $this->getCategories());

        if ($catIds === []) {
            $query->where('1 = 0');
        } else {
            $query->whereIn($db->quoteName('a.catid'), $catIds);
        }

        $orderColumn = $this->getState('linklist.ordering') === 'title' ? 'a.title' : 'a.ordering';
        $direction   = $this->getState('linklist.direction') === 'DESC' ? 'DESC' : 'ASC';

        $query->order($db->quoteName($orderColumn) . ' ' . $direction);

        return $query;
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();

        parent::populateState($ordering, $direction);

        // Menu item params merged with component params; works without an active menu item too
        $params = $app->getParams();
        $this->setState('params', $params);

        // Categories: new multi-select param, fallback to the old single request value (menu items from 0.1)
        $catIds = (array) $params->get('catids', []);

        if ($catIds === [] || $catIds === ['']) {
            $legacy = $app->getInput()->getInt('category_id', 0);
            $catIds = $legacy ? [$legacy] : [];
        }

        $this->setState('filter.category_ids', array_values(array_filter(array_map('intval', $catIds))));
        $this->setState('filter.include_subcategories', (bool) $params->get('include_subcategories', 1));
        $this->setState('linklist.ordering', $params->get('ordering', 'ordering'));
        $this->setState('linklist.direction', $params->get('direction', 'ASC'));

        // Show all links, no pagination
        $this->setState('list.start', 0);
        $this->setState('list.limit', 0);
    }
}
