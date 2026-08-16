<?php
namespace SpeckenbachNet\Component\LinkListM\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController {

    public function display($cachable = false, $urlparams = array())
    {
        $view = $this->getView('linklist', 'html');
        $view->display();
    }
}