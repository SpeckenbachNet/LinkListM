<?php

namespace SpeckenbachNet\Component\LinkListM\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Factory;

class LinklistsModel extends ListModel
{
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);
        $ordering = $this->getState('ordering', 'ordering');
        $direction = $this->getState('direction', 'ASC');

        $query->select(
            'a.id, a.title, a.url, a.description, a.catid, a.access, a.state, c.title AS category_title'
        )
            ->from($db->quoteName('#__linklistm_links', 'a'))
            ->leftJoin(
                $db->quoteName('#__categories', 'c')
                . ' ON c.id = a.catid'
        );

        // Only show published links
        $query->where($db->quoteName('a.state') . ' = 1');

        // Apply Joomla access level restrictions
        $user = $this->getCurrentUser();

        if (!$user->authorise('core.admin')) {
            $query->whereIn(
                $db->quoteName('a.access'),
                $user->getAuthorisedViewLevels()
            );
        }

        // Filter by selected category
        $$categoryId = $this->getState('filter.category_id');

        if (is_numeric($categoryId)) {
            $categoryIds = [(int) $categoryId];

            if ($this->getState('include_subcategories')) {
                $categories = Categories::getInstance('linklistm');
                $category = $categories->get($categoryId);

                foreach ($category->getChildren(true) as $child) {
                    $categoryIds[] = (int) $child->id;
                }
            }

            $query->whereIn(
                $db->quoteName('a.catid'),
                $categoryIds
            );
        }

        // Apply menu item ordering
        switch ($ordering) {
            case 'title':
                $orderColumn = 'a.title';
                break;

            case 'category':
                $orderColumn = 'c.title';
                break;

            default:
                $orderColumn = 'a.ordering';
        }

        $query->order(
            $db->quoteName($orderColumn) . ' ' . ($direction === 'DESC' ? 'DESC' : 'ASC')
        );

        return $query;
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();

        $this->setState('filter.category_id', $input->get('category_id', '', 'cmd'));
        $this->setState('include_subcategories', $input->get('include_subcategories', 0, 'int'));
        $this->setState('ordering', $input->get('ordering', 'ordering', 'cmd'));
        $this->setState('direction', $input->get('direction', 'ASC', 'cmd'));
        $this->setState('show_description', $input->get('show_description', 1, 'int'));

        parent::populateState($ordering, $direction);
    }
}