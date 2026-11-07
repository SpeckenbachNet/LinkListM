<?php

/**
 * Link list output, shared by the menu item view and the content plugin.
 *
 * @var array $displayData  see Renderer::render()
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use SpeckenbachNet\Component\LinkListM\Administrator\Helper\UrlHelper;

$blocks          = $displayData['blocks'];
$showDescription = $displayData['showDescription'];
$showDomain      = $displayData['showDomain'];
$newWindow       = $displayData['newWindow'];
$ownHost         = $displayData['ownHost'];
$tiles           = $displayData['tiles'];
$columns         = $displayData['columns'];

$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

// Renders the links of one category
$renderLinks = static function (array $items) use ($e, $showDescription, $showDomain, $newWindow, $ownHost): void {
    if ($items === []) {
        return;
    }
    ?>
    <ul class="linklistm-links">
        <?php foreach ($items as $item) :
            $external    = UrlHelper::isExternal($item->url, $ownHost);
            $blank       = $newWindow && $external;
            $description = trim((string) $item->description);
            ?>
            <li class="linklistm-link">
                <a class="linklistm-link__title" href="<?php echo $e($item->url); ?>"
                    <?php echo $blank ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <?php echo $e($item->title); ?>
                    <?php if ($external) : ?>
                        <span class="linklistm-link__ext" aria-hidden="true">↗</span>
                    <?php endif; ?>
                    <?php if ($blank) : ?>
                        <span class="visually-hidden"><?php echo Text::_('COM_LINKLISTM_OPENS_NEW_WINDOW'); ?></span>
                    <?php endif; ?>
                </a>
                <?php if ($showDomain && $item->domain !== '') : ?>
                    <div class="linklistm-link__domain">
                        <span class="icon-globe" aria-hidden="true"></span>
                        <?php echo $e($item->domain); ?>
                    </div>
                <?php endif; ?>
                <?php if ($showDescription && strip_tags($description) !== '') : ?>
                    <div class="linklistm-link__desc"><?php echo $description; ?></div>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
};
?>
<div class="linklistm-blocks">
    <?php foreach ($blocks as $block) : ?>
        <section class="linklistm-block">
            <h2 class="linklistm-block__title"><?php echo $e($block['title']); ?></h2>

            <?php $renderLinks($block['items']); ?>

            <?php foreach ($block['sections'] as $section) :
                $level = min($section['depth'] + 1, 3);
                $tag   = $level === 2 ? 'h3' : 'h4';
                ?>
                <div class="linklistm-section linklistm-section--level<?php echo $level; ?>">
                    <<?php echo $tag; ?> class="linklistm-section__title">
                        <?php foreach ($section['path'] as $ancestor) : ?>
                            <span class="linklistm-section__path"><?php echo $e($ancestor); ?> ›</span>
                        <?php endforeach; ?>
                        <?php echo $e($section['title']); ?>
                    </<?php echo $tag; ?>>

                    <?php $renderLinks($section['items']); ?>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
</div>
