<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use SpeckenbachNet\Component\LinkListM\Site\Helper\Renderer;

/** @var \SpeckenbachNet\Component\LinkListM\Site\View\Linklists\HtmlView $this */
?>
<div class="com-linklistm">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <?php if ($this->blocks === []) : ?>
        <p class="linklistm-empty"><?php echo Text::_('COM_LINKLISTM_NO_LINKS'); ?></p>
    <?php else : ?>
        <?php echo Renderer::render($this->blocks, $this->options); ?>
    <?php endif; ?>
</div>
