<?php

namespace SpeckenbachNet\Component\LinkListM\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;

class LinklistModel extends AdminModel
{
    protected $text_prefix = 'COM_LINKLISTM';

    // Define table type and prefix used to find LinklistTable
    public function getTable($type = 'Linklist', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    // Load form for creating or editing a link
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_linklistm.linklist',
            'linklist',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    // Load previously submitted form data or current link data
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_linklistm.edit.linklist.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    protected function prepareTable($table)
    {
        if (empty($table->alias)) {
            $table->alias = $table->title;
        }

        $table->alias = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe($table->alias);

        if (empty($table->id)) {
            if (empty($table->ordering)) {
                $db = $this->getDatabase();

                $query = $db->getQuery(true)
                    ->select('MAX(ordering)')
                    ->from($db->quoteName('#__linklistm_links'));

                $db->setQuery($query);

                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        }
    }
}