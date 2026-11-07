<?php

namespace SpeckenbachNet\Component\LinkListM\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    // Default view when no view is given in the URL
    protected $default_view = 'linklists';

    // Use parent controller's display method with given parameters
    public function display($cachable = false, $urlparams = array())
    {
        return parent::display($cachable, $urlparams);
    }
}