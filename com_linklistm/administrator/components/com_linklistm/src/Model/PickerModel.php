<?php

namespace SpeckenbachNet\Component\LinkListM\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Data for the editor button's picker.
 */
class PickerModel extends BaseDatabaseModel
{
    /**
     * Published categories in tree order.
     *
     * @return  object[]  id, title, level
     */
    public function getCategories(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'title', 'level']))
            ->from($db->quoteName('#__categories'))
            ->where($db->quoteName('extension') . ' = ' . $db->quote('com_linklistm'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('lft') . ' ASC');

        return $db->setQuery($query)->loadObjectList();
    }
}
