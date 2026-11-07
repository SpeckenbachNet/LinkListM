<?php

namespace SpeckenbachNet\Component\LinkListM\Administrator\View\Picker;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Picker opened by the editor button (tmpl=component): builds a {linklistm ...} tag.
 */
class HtmlView extends BaseHtmlView
{
    /** @var object[] */
    public array $categories = [];

    public function display($tpl = null)
    {
        $this->categories = $this->getModel()->getCategories();

        $this->getDocument()->getWebAssetManager()
            ->registerAndUseScript('com_linklistm.picker', 'com_linklistm/picker.js', [], ['defer' => true]);

        parent::display($tpl);
    }
}
