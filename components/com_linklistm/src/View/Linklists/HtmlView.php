<?php

namespace SpeckenbachNet\Component\LinkListM\Site\View\Linklists;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $items;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        $this->items = $model->getItems();

        $this->itemsByCategory = [];

        foreach ($this->items as $item) {
            $this->itemsByCategory[$item->catid][] = $item;
        }

        uksort(
            $this->itemsByCategory,
            function ($a, $b) {
                return $this->itemsByCategory[$a][0]->lft
                    <=> $this->itemsByCategory[$b][0]->lft;
            }
        );

        //$this->params = $model->getState('params');
        $this->showDescription = $model->getState('show_description', 1);

        parent::display($tpl);
    }
}