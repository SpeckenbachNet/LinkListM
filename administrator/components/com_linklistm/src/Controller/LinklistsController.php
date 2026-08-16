<?php

namespace SpeckenbachNet\Component\LinkListM\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class LinklistsController extends AdminController
{
     public function getModel($name = 'Linklist', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
} 