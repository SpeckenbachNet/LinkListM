<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use SpeckenbachNet\Component\LinkListM\Administrator\Helper\UrlHelper;

/** @var \SpeckenbachNet\Component\LinkListM\Site\View\Linklists\HtmlView $this */

// Renders the links of one category
$renderLinks = function (array $items): void {
    if ($items === []) {
        return;
    }
    ?>
    <ul class="linklistm-links">
        <?php foreach ($items as $item) :
            $external    = UrlHelper::isExternal($item->url, $this->ownHost);
            $newWindow   = $this->newWindow && $external;
            $description = trim((string) $item->description);
            ?>
            <li class="linklistm-link">
                <a class="linklistm-link__title" href="<?php echo $this->escape($item->url); ?>"
                    <?php echo $newWindow ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <?php echo $this->escape($item->title); ?>
                    <?php if ($external) : ?>
                        <span class="linklistm-link__ext" aria-hidden="true">↗</span>
                    <?php endif; ?>
                    <?php if ($newWindow) : ?>
                        <span class="visually-hidden"><?php echo Text::_('COM_LINKLISTM_OPENS_NEW_WINDOW'); ?></span>
                    <?php endif; ?>
                </a>
                <?php if ($this->showDomain && $item->domain !== '') : ?>
                    <div class="linklistm-link__domain">
                        <span class="icon-globe" aria-hidden="true"></span>
                        <?php echo $this->escape($item->domain); ?>
                    </div>
                <?php endif; ?>
                <?php if ($this->showDescription && strip_tags($description) !== '') : ?>
                    <div class="linklistm-link__desc"><?php echo $description; ?></div>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
};
?>

<div class="com-linklistm linklistm<?php echo $this->tiles ? ' linklistm--tiles linklistm--cols-' . $this->columns : ''; ?>">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <?php if ($this->blocks === []) : ?>
        <p class="linklistm-empty"><?php echo Text::_('COM_LINKLISTM_NO_LINKS'); ?></p>
    <?php endif; ?>

    <div class="linklistm-blocks">
    <?php foreach ($this->blocks as $block) : ?>
        <section class="linklistm-block">
            <h2 class="linklistm-block__title"><?php echo $this->escape($block['title']); ?></h2>

            <?php $renderLinks($block['items']); ?>

            <?php foreach ($block['sections'] as $section) :
                $level = min($section['depth'] + 1, 3);
                $tag   = $level === 2 ? 'h3' : 'h4';
                ?>
                <div class="linklistm-section linklistm-section--level<?php echo $level; ?>">
                    <<?php echo $tag; ?> class="linklistm-section__title">
                        <?php foreach ($section['path'] as $ancestor) : ?>
                            <span class="linklistm-section__path"><?php echo $this->escape($ancestor); ?> ›</span>
                        <?php endforeach; ?>
                        <?php echo $this->escape($section['title']); ?>
                    </<?php echo $tag; ?>>

                    <?php $renderLinks($section['items']); ?>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
    </div>
</div>
