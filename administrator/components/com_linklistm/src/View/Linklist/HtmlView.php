<?php

namespace SpeckenbachNet\Component\LinkListM\Administrator\View\Linklist;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $item;
    protected $form;
    protected $state;

    public function display($tpl = null)
    {
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');
        $this->state = $this->get('State');

        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $user   = $this->getCurrentUser();
        $isNew  = ($this->item->id == 0);
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(
            Text::_($isNew ? 'COM_LINKLISTM_MANAGER_LINKLIST_NEW' : 'COM_LINKLISTM_MANAGER_LINKLIST_EDIT'),
            'link'
        );

        $toolbar->apply('linklist.apply');
        $toolbar->save('linklist.save');

        if (!$isNew) {
            $toolbar->save2new('linklist.save2new');
        } else {
            $toolbar->save2new('linklist.save2new');
        }

        $toolbar->cancel('linklist.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}