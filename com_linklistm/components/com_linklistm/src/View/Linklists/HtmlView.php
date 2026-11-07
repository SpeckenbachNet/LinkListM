<?php

namespace SpeckenbachNet\Component\LinkListM\Site\View\Linklists;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use SpeckenbachNet\Component\LinkListM\Site\Helper\Renderer;

class HtmlView extends BaseHtmlView
{
    /**
     * Blocks to render, see CategoryTree::build()
     *
     * @var array
     */
    protected $blocks = [];

    /**
     * @var \Joomla\Registry\Registry
     */
    protected $params;

    /**
     * Display options for Renderer::render(), from the menu item
     *
     * @var array
     */
    protected $options = [];

    public function display($tpl = null)
    {
        $model = $this->getModel();

        $this->params = $model->getState('params');
        $this->blocks = Renderer::blocks($model);

        $this->options = [
            'show_description' => (bool) $this->params->get('show_description', 0),
            'show_domain'      => (bool) $this->params->get('show_domain', 1),
            'new_window'       => (bool) $this->params->get('new_window', 1),
            'display_style'    => (string) $this->params->get('display_style', 'list'),
            'columns'          => (int) $this->params->get('columns', 2),
        ];

        $this->prepareDocument();

        parent::display($tpl);
    }

    protected function prepareDocument(): void
    {
        $app  = Factory::getApplication();
        $menu = $app->getMenu()->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        }

        $this->setDocumentTitle($this->params->get('page_title', ''));

        if ($this->params->get('menu-meta_description')) {
            $this->getDocument()->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->getDocument()->setMetaData('robots', $this->params->get('robots'));
        }
    }
}
