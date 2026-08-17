<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');
$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
$assoc     = Associations::isEnabled();
if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_linklistm&task=linklists.saveOrderAjax&tmpl=component';
    HTMLHelper::_('draggablelist.draggable');
}

?>

<form action="<?php echo Route::_('index.php?option=com_linklistm&view=linklists'); ?>" method="post" name="adminForm" id="adminForm">

    <?php
        // Search tools bar
        echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
    ?>
    <?php if (empty($this->items)) : ?>
        <div class="alert alert-info">
            <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>
        <div class="table-responsive">
            <table class="table" id="linklist">
                <thead>
                    <tr>
                        <th class="w-1 text-center">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                        </th>
                        <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
                        </th>
                        <th scope="col" style="min-width:85px" class="w-1 text-center">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_LINKLISTM_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>

                        <th scope="col">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_LINKLISTM_CATEGORY', 'c.title', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-10 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-5 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>

                <tbody <?php if ($saveOrder) :
                        ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php
                            endif; ?>>
                    <?php foreach ($this->items as $i => $item) :
                        ?>
                        <?php $item->cat_link = Route::_('index.php?option=com_categories&extension=com_linklistm&task=edit&type=other&cid[]=' . $item->catid); ?>
                        <?php $canCreate      = $user->authorise('core.create', 'com_linklistm.category.' . $item->catid); ?>
                        <?php $canEdit        = $user->authorise('core.edit', 'com_linklistm.category.' . $item->catid); ?>
                        <?php $canCheckin     = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->id || !$item->checked_out; ?>
                        <?php $canEditOwn     = $user->authorise('core.edit.own', 'com_linklistm.category.' . $item->catid) && $item->created_by == $user->id; ?>
                        <?php $canChange      = $user->authorise('core.edit.state', 'com_linklistm.category.' . $item->catid) && $canCheckin; ?>
                        <tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->catid; ?>">
                            <td class="text-center">
                                <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?>
                            </td>

                            <td class="text-center d-none d-md-table-cell">
                                <?php
                                $iconClass = '';
                                if (!$canChange) {
                                    $iconClass = ' inactive';
                                } elseif (!$saveOrder) {
                                    $iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
                                }
                                ?>
                                <span class="sortable-handler<?php echo $iconClass; ?>">
                                    <span class="icon-ellipsis-v" aria-hidden="true"></span>
                                </span>
                                <?php if ($canChange && $saveOrder) :
                                    ?>
                                    <input type="text" name="order[]" size="5"
                                            value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
                                    <?php
                                endif; ?>
                            </td>

                            <td class="text-center">
                                <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'linklists.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
                            </td>

                            <td>
                                <div>
                                    <?php if ($item->checked_out) : ?>
                                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'weblinks.', $canCheckin); ?>
                                    <?php endif; ?>
                                        <?php if ($canEdit || $canEditOwn) : ?>
                                            <a href="<?php echo Route::_('index.php?option=com_linklistm&task=linklist.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->title); ?>">
                                                <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        <?php else : ?>
                                            <?php echo $this->escape($item->title); ?>
                                        <?php endif; ?>

                                    <div class="small">
                                        <?php echo Text::_('JFIELD_ALIAS_LABEL'); ?>:
                                        <?php echo $this->escape($item->alias); ?>
                                    </div>
                                    <div class="small text-muted">
                                        <?php echo htmlspecialchars($item->url, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <a href="<?php echo $item->cat_link; ?>">
                                    <?php echo htmlspecialchars($item->category_title, ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </td>

                            <td class="small d-none d-md-table-cell">
                                <?php echo $this->escape($item->access_level); ?>
                            </td>

                            <td class="d-none d-md-table-cell">
                                <?php echo (int) $item->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>

</form>