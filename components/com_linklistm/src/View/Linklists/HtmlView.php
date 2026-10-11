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

        // Group links by category
        $this->itemsByCategory = [];

        foreach ($this->items as $item) {
            $this->itemsByCategory[$item->catid][] = $item;
        }

        // Build category structure
        $this->categories = [];

        foreach ($this->items as $item) {
            if (!isset($this->categories[$item->catid])) {
                $this->categories[$item->catid] = [
                    'id'        => (int) $item->catid,
                    'title'     => $item->category_title,
                    'parent_id' => (int) $item->parent_id,
                    'level'     => (int) $item->level,
                    'lft'       => (int) $item->lft,
                    'items'     => $this->itemsByCategory[$item->catid],
                    'children'  => [],
                ];
            }
        }

        // Build parent/child relationships
        foreach ($this->categories as $id => &$category) {
            if (
                $category['parent_id'] !== 1
                && isset($this->categories[$category['parent_id']])
            ) {
                $this->categories[$category['parent_id']]['children'][] =& $category;
            }
        }

        unset($category);

        $this->rootCategories = [];

        foreach ($this->categories as $category) {
            if (
                $category['parent_id'] === 1
                || !isset($this->categories[$category['parent_id']])
            ) {
                $this->rootCategories[] = $category;
            }
        }

        // Sort categories according to Joomla category tree
        uasort(
            $this->categories,
            fn($a, $b) => $a['lft'] <=> $b['lft']
        );

        $this->showDescription = $model->getState('show_description', 1);

        parent::display($tpl);
    }
}