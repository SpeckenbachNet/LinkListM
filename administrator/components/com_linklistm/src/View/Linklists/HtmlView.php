<?php

namespace SpeckenbachNet\Component\LinkListM\Administrator\View\LinkLists;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Weblinks\Administrator\Model\WeblinksModel;

class HtmlView extends BaseHtmlView {

    protected $items;
    protected $pagination;
    protected $state;
    public $filterForm;
    public $activeFilters;
    private $isEmptyState = false;

    function display($tpl = null) {

        $model = $this->getModel();  // Get model
		$this->state = $model->getState();  // Get current state of the list, e.g. sorting and filter settings
		$this->items = $model->getItems();  // Get itmes for list
		$this->pagination    = $model->getPagination();  // Get page navigation
        $this->filterForm    = $model->getFilterForm();  // Get form for the search/filter tools
        $this->activeFilters = $model->getActiveFilters();  // Get currently applied filters

		// Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

		// Check if list is empty
        if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState')) {
            $this->setLayout('emptystate');
        }

		// Create Toolbar
        $this->addToolbar();
		// Show list
        parent::display($tpl);
    }
	
protected function addToolbar()
    {
		// Get the current user's permissions for the category in the current filter.
        $canDo = ContentHelper::getActions('com_linklistm', 'category', $this->state->get('filter.category_id'));
        
		// Get current user
		$user  = $this->getCurrentUser();

        // Get the toolbar object instance
        $toolbar = $this->getDocument()->getToolbar();

		// Set page title in header ('link' = CSS-Class for Link-Icon)
        ToolbarHelper::title(Text::_('COM_LINKLISTM_MANAGER_LINKLIST'), 'link');

		// Show the "New" button if the user can create links in the current category
		// or is authorized to create links in at least one category.
        if ($canDo->get('core.create') || \count($user->getAuthorisedCategories('com_linklistm', 'core.create')) > 0) {
            ToolbarHelper::addNew('linklists.add');
        }

		// If list is not empty and user can edit create action menu
        if (!$this->isEmptyState && $canDo->get('core.edit.state')) {
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

			// Child entries
            $childBar = $dropdown->getChildToolbar();
            $childBar->publish('linnklist.publish')->listCheck(true);
            $childBar->unpublish('linklists.unpublish')->listCheck(true);
            $childBar->archive('linklists.archive')->listCheck(true);

			// If user is admin create checkin entry
            if ($user->authorise('core.admin')) {
                $childBar->checkin('linklists.checkin')->listCheck(true);
            }

			// Show the "Trash" action unless the current list displays trashed items.
            if ($this->state->get('filter.published') != -2) {
                $childBar->trash('linklists.trash')->listCheck(true);
            }

            // Add a batch button to change properties for multiple entries
            if (
                $user->authorise('core.create', 'com_linklistm')
                && $user->authorise('core.edit', 'com_linklistm')
                && $user->authorise('core.edit.state', 'com_linklistm')
            ) {
                $childBar->popupButton('batch')
                    ->text('JTOOLBAR_BATCH')
                    ->selector('collapseModal')
                    ->listCheck(true);
            }
        }

		// Show "Empty Trash" button if viewing trashed items and user has delete permission.
        if (!$this->isEmptyState && $this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
            $toolbar->delete('linklists.delete')
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

		// Show Options button if user has access rights.
        if ($user->authorise('core.admin', 'com_linklistm') || $user->authorise('core.options', 'com_weblinks')) {
            ToolbarHelper::preferences('com_linklistm');
        }

        ToolbarHelper::help('Components_Linklists_Links');
    }
}