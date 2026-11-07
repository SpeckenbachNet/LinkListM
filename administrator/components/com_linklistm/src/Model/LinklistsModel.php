<?php

namespace SpeckenbachNet\Component\LinkListM\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

class LinklistsModel extends ListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'catid', 'a.catid',
                'category_title',
                'state', 'a.state',
                'access', 'a.access',
                'access_level',
                'ordering', 'a.ordering',
            ];
        }

        // Pass configuration to parent ListModel
        parent::__construct($config, $factory);
    }

    // Build query to retrieve link list data
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);
            
        $query->select('a.id, a.alias, a.title, a.url, a.catid, a.access, a.state, a.ordering, a.created_by, a.publish_up, a.publish_down, a.checked_out, a.checked_out_time, c.title AS category_title, v.title AS access_level, uc.name AS editor')
            ->from($db->quoteName('#__linklistm_links', 'a'))
            ->leftJoin(
                $db->quoteName('#__categories', 'c')
                . ' ON c.id = a.catid'
            )
            ->leftJoin(
                $db->quoteName('#__viewlevels', 'v')
                . ' ON v.id = a.access'
            )
            ->leftJoin(
                $db->quoteName('#__users', 'uc')
                . ' ON uc.id = a.checked_out'
            );

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $search = substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :id')
                    ->bind(':id', $search, ParameterType::INTEGER);
            } else {
                $search = '%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%');
                $query->where(
                    '(' . $db->quoteName('a.title') . ' LIKE :title OR '
                    . $db->quoteName('a.url') . ' LIKE :url)'
                )
                    ->bind(':title', $search)
                    ->bind(':url', $search);
            }
        }

        // Filter by published state
        $published = (string) $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where($db->quoteName('a.state') . ' = :state')
                ->bind(':state', $published, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->whereIn($db->quoteName('a.state'), [0, 1]);
        }

        // Filter by access level
        $access = $this->getState('filter.access');

        if ($access) {
            $query->where($db->quoteName('a.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        }

        // Filter by category
        $categoryId = $this->getState('filter.category_id');

        if (is_numeric($categoryId)) {
            $query->where($db->quoteName('a.catid') . ' = :catid')
                ->bind(':catid', $categoryId, ParameterType::INTEGER);
        }

        // Add list ordering
        $orderCol  = $this->state->get('list.ordering', 'a.title');
        $orderDirn = $this->state->get('list.direction', 'ASC');

        if ($orderCol == 'a.ordering' || $orderCol == 'category_title') {
            $orderCol = 'c.title ' . $orderDirn . ', a.ordering';
        }

        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    protected function populateState($ordering = 'a.title', $direction = 'asc') {

        // Initialize list state with default ordering
        parent::populateState($ordering, $direction);
    }
}