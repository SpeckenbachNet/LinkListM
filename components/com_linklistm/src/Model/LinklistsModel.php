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
            'a.id, a.title, a.url, a.description, a.catid, a.access, a.state, c.title AS category_title, c.parent_id, c.level, c.lft, c.rgt'
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
        // $categoryId = $this->getState('filter.category_id');

        // if (is_numeric($categoryId)) {
        //     $categoryIds = [(int) $categoryId];

    //         if ($this->getState('include_subcategories')) {
    //              $options = [
    //     'table'      => '#__linklistm_links',
    //     'extension'  => 'com_linklistm',
    //     'field'      => 'catid',
    //     'key'        => 'id',
    //     'statefield' => 'state',
    // ];

    // $categories = Categories::getInstance('linklistm', $options);
    // $category = $categories->get($categoryId);

    //             foreach ($category->getChildren(true) as $child) {
    //                 $categoryIds[] = (int) $child->id;
    //             }
    //         }

    // Filter by selected category
        $categoryId = $this->getState('filter.category_id');

        if (is_numeric($categoryId)) {
            if ($this->getState('include_subcategories')) {
                $query->where(
                    $db->quoteName('c.lft') . ' >= ' .
                    '(SELECT lft FROM ' . $db->quoteName('#__categories') .
                    ' WHERE id = ' . (int) $categoryId . ')'
                );

                $query->where(
                    $db->quoteName('c.rgt') . ' <= ' .
                    '(SELECT rgt FROM ' . $db->quoteName('#__categories') .
                    ' WHERE id = ' . (int) $categoryId . ')'
                );
            } else {
                $query->where(
                    $db->quoteName('a.catid') . ' = ' . (int) $categoryId
                );
            }
        }

        // Apply menu item ordering
        switch ($ordering) {
            case 'title':
                $orderColumn = 'a.title';
                break;

            default:
                $orderColumn = 'a.ordering';
        }

        $query->order(
            'c.lft ASC, ' .
            $db->quoteName($orderColumn) . ' ' . 
            ($direction === 'DESC' ? 'DESC' : 'ASC')
        );

        return $query;
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();

        $this->setState(
            'filter.category_id',
            $input->get('category_id', '', 'cmd')
        );

        $menuItem = $app->getMenu()->getActive();
        $params = $menuItem->getParams();

        $this->setState(
            'include_subcategories',
            $params->get('include_subcategories', 0)
        );

        $this->setState(
            'ordering',
            $params->get('ordering', 'ordering')
        );

        $this->setState(
            'direction',
            $params->get('direction', 'ASC')
        );

        $this->setState(
            'show_description',
            $params->get('show_description', 1)
        );

        parent::populateState($ordering, $direction);
    }
}