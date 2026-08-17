<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$input = Factory::getApplication()->getInput();
$input->set('hidemainmenu', true);

?>

<form action="<?php echo Route::_('index.php?option=com_linklistm&task=linklist.save'); ?>"
      method="post" name="adminForm" id="linklist-form" class="form-validate" aria-label="<?php echo Text::_('COM_LINKLISTM_MANAGER_LINKLIST', true); ?>">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', empty($this->item->id) ? Text::_('COM_LINKLISTM_NEW_LINK', true) : Text::_('COM_LINKLISTM_EDIT_LINK', true)); ?>
        <div class="row">
          <div class="col-md-9">
             <div class="form-vertical">
                <div>
                      <fieldset class="adminform">
                            <?php echo $this->form->renderField('url'); ?>
                            <?php echo $this->form->renderField('description'); ?>
                     </fieldset>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>


    <input type="hidden" name="task" value="">
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>