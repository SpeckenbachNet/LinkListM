<?php

namespace SpeckenbachNet\Component\LinkListM\Site\View\Linklists;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;
use SpeckenbachNet\Component\LinkListM\Administrator\Helper\UrlHelper;
use SpeckenbachNet\Component\LinkListM\Site\Helper\CategoryTree;

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

    protected $showDescription = false;
    protected $showDomain      = true;
    protected $newWindow       = true;
    protected $ownHost         = '';

    public function display($tpl = null)
    {
        $model = $this->getModel();

        $this->params = $model->getState('params');
        $items        = $model->getItems() ?: [];

        // Prepare link data for the template
        foreach ($items as $item) {
            $item->url    = UrlHelper::normalize((string) $item->url);
            $item->domain = UrlHelper::domain($item->url);
        }

        $this->blocks = CategoryTree::build(
            $model->getCategories(),
            $items,
            (array) $model->getState('filter.category_ids', []),
            (bool) $model->getState('filter.include_subcategories', true)
        );

        $this->showDescription = (bool) $this->params->get('show_description', 0);
        $this->showDomain      = (bool) $this->params->get('show_domain', 1);
        $this->newWindow       = (bool) $this->params->get('new_window', 1);
        $this->ownHost         = (string) Uri::getInstance()->getHost();

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

        // Own stylesheet, can be overridden in the template (css/com_linklistm/linklistm.css)
        $this->getDocument()->getWebAssetManager()
            ->registerAndUseStyle('com_linklistm.site', 'com_linklistm/linklistm.css');
    }
}
