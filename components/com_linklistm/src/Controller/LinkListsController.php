<?php

namespace SpeckenbachNet\Component\LinkListM\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class LinklistsController extends BaseController
{
     public function getModel($name = 'Linklists', $prefix = 'Site', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}