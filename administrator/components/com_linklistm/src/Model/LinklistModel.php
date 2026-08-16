<?php

namespace SpeckenbachNet\Component\LinkListM\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;

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
        $data = $this->getApplication()->getUserState('com_linklistm.edit.linklist.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }
}