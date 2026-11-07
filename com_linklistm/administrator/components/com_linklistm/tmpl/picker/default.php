<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \SpeckenbachNet\Component\LinkListM\Administrator\View\Picker\HtmlView $this */

// One group of radio buttons; the first option is the default and is left out of the tag
$choice = function (string $name, string $label, array $options) {
    ?>
    <fieldset class="mb-3">
        <legend class="form-label fs-6 mb-1"><?php echo Text::_($label); ?></legend>
        <?php foreach ($options as $value => $text) :
            $id = 'llm-' . $name . '-' . $value;
            ?>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo $value; ?>"
                    <?php echo $value === array_key_first($options) ? ' checked' : ''; ?>>
                <label class="form-check-label" for="<?php echo $id; ?>"><?php echo Text::_($text); ?></label>
            </div>
        <?php endforeach; ?>
    </fieldset>
    <?php
};?>
<form class="p-3 linklistm-picker" id="linklistm-picker" onsubmit="return false;">
    <fieldset class="mb-3">
        <legend class="form-label fs-6"><?php echo Text::_('COM_LINKLISTM_PICKER_CATEGORIES'); ?></legend>

        <?php if ($this->categories === []) : ?>
            <p class="text-muted"><?php echo Text::_('COM_LINKLISTM_DASHBOARD_NO_CATEGORIES'); ?></p>
        <?php endif; ?>

        <?php foreach ($this->categories as $category) : ?>
            <div class="form-check" style="margin-left: <?php echo max(0, (int) $category->level - 1) * 1.5; ?>rem">
                <input class="form-check-input" type="checkbox" name="cat" value="<?php echo (int) $category->id; ?>" id="llm-cat-<?php echo (int) $category->id; ?>">
                <label class="form-check-label" for="llm-cat-<?php echo (int) $category->id; ?>">
                    <?php echo $this->escape($category->title); ?>
                    <span class="small text-muted">(<?php echo Text::sprintf('COM_LINKLISTM_DASHBOARD_CATEGORY_ID', (int) $category->id); ?>)</span>
                </label>
            </div>
        <?php endforeach; ?>
    </fieldset>

    <?php $choice('sub', 'COM_LINKLISTM_FIELD_INCLUDE_SUBCATEGORIES', ['1' => 'JYES', '0' => 'JNO']); ?>
    <?php $choice('style', 'COM_LINKLISTM_FIELD_DISPLAY_STYLE', ['list' => 'COM_LINKLISTM_DISPLAY_STYLE_LIST', 'tiles' => 'COM_LINKLISTM_DISPLAY_STYLE_TILES']); ?>
    <div id="linklistm-picker-cols">
        <?php $choice('cols', 'COM_LINKLISTM_FIELD_COLUMNS', ['2' => '2', '3' => '3']); ?>
    </div>
    <?php $choice('desc', 'COM_LINKLISTM_FIELD_SHOW_DESCRIPTION', ['1' => 'JYES', '0' => 'JNO']); ?>
    <?php $choice('domain', 'COM_LINKLISTM_FIELD_SHOW_DOMAIN', ['1' => 'JYES', '0' => 'JNO']); ?>

    <div class="mb-3">
        <div class="form-label"><?php echo Text::_('COM_LINKLISTM_PICKER_TAG'); ?></div>
        <code id="linklistm-picker-tag" class="d-inline-block p-2 border rounded">{linklistm}</code>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" id="linklistm-picker-cancel"><?php echo Text::_('JCANCEL'); ?></button>
        <button type="button" class="btn btn-primary" id="linklistm-picker-insert"><?php echo Text::_('COM_LINKLISTM_PICKER_INSERT'); ?></button>
    </div>
</form>
