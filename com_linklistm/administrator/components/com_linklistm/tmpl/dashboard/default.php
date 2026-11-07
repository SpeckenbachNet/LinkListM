<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \SpeckenbachNet\Component\LinkListM\Administrator\View\Dashboard\HtmlView $this */

$info  = $this->info;
$tiles = $this->tiles;
$e     = fn (?string $value): string => $this->escape((string) $value);
$link  = fn (string $query): string => Route::_('index.php?option=com_linklistm&' . $query);

// Joomla's update screens; only users allowed to manage extensions get a link
$installerLink = fn (string $view): string => $this->canUpdate ? Route::_('index.php?option=com_installer&view=' . $view) : '';

// Coloured accents only (border + icon, no filled rows: Atum dark mode)
Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineStyle(
    '.llm-todo { border-left: 4px solid var(--llm-c); padding: .25rem 0 .25rem .6rem; margin-bottom: .4rem; }'
    . ' .llm-todo .llm-todo-icon, .llm-check-warn { color: var(--llm-c); }'
    . ' .llm-todo-yellow, .llm-check-warn { --llm-c: var(--bs-orange, #fd7e14); }'
    . ' .llm-todo-danger { --llm-c: var(--bs-danger, #dc3545); }'
    . ' .llm-check-danger { color: var(--bs-danger, #dc3545); }'
    . ' .llm-check-ok { color: var(--bs-success, #198754); }'
    . ' .llm-cat { display: flex; justify-content: space-between; gap: 1rem; padding: .3rem 0; border-bottom: 1px solid var(--bs-border-color, #dee2e6); }'
    . ' .llm-cat:last-child { border-bottom: 0; }'
    . ' .llm-cat-count { white-space: nowrap; font-variant-numeric: tabular-nums; }'
    . ' .quick-icons.llm-quickicons { background-color: transparent; padding: 0; }'
    . ' .quickicon a.llm-tile-donate { --bg-color: #f0ad4e; --bg-color-hvr: #df8a13; --text-color: #000;'
    . ' --text-color-hvr: #000; --icon-color: #000; --icon-color-hvr: #000; }'
);

/**
 * One quick-icon tile in Joomla's dashboard style (works in light and dark mode).
 */
$tile = function (string $url, string $icon, string $name, ?int $amount = null, string $class = '', bool $external = false) use ($e): string {
    return '<li class="quickicon quickicon-single"><a href="' . $url . '"'
        . ($class !== '' ? ' class="' . $class . '"' : '')
        . ($external ? ' target="_blank" rel="noopener"' : '') . '>'
        . '<div class="quickicon-info">'
        . '<div class="quickicon-icon"><div class="' . $icon . '" aria-hidden="true"></div></div>'
        . ($amount !== null ? '<div class="quickicon-amount" aria-hidden="true">' . $amount . '</div>' : '')
        . '</div>'
        . '<div class="quickicon-name d-flex align-items-end">' . $e($name)
        . ($amount !== null ? '<span class="visually-hidden">: ' . $amount . '</span>' : '') . '</div>'
        . '</a></li>';
};
?>
<div class="card mb-3">
    <div class="card-body">
        <nav class="quick-icons llm-quickicons" aria-label="<?php echo Text::_('COM_LINKLISTM_DASHBOARD_QUICKICONS'); ?>">
            <ul class="nav flex-wrap">
                <?php echo $tile($link('view=linklists'), 'icon-link', Text::_('COM_LINKLISTM_MENU_LINKS'), (int) $tiles['links']); ?>
                <?php echo $tile(Route::_('index.php?option=com_categories&extension=com_linklistm'), 'icon-folder', Text::_('COM_LINKLISTM_MENU_CATEGORIES'), (int) $tiles['categories']); ?>
                <?php if ($this->canCreate) : ?>
                    <?php echo $tile($link('task=linklist.add'), 'icon-plus', Text::_('COM_LINKLISTM_NEW_LINK')); ?>
                <?php endif; ?>
                <?php if ($this->canOptions) : ?>
                    <?php echo $tile(Route::_('index.php?option=com_config&view=component&component=com_linklistm'), 'icon-cog', Text::_('JOPTIONS')); ?>
                <?php endif; ?>
                <?php echo $tile($e($this->donateUrl), 'fa-solid fa-mug-hot', Text::_('COM_LINKLISTM_DASHBOARD_DONATE'), null, 'llm-tile-donate', true); ?>
            </ul>
        </nav>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h5"><?php echo Text::_('COM_LINKLISTM_DASHBOARD_CATEGORIES'); ?></h2>

                <?php if ($this->categories === []) : ?>
                    <p class="mb-0"><?php echo Text::_('COM_LINKLISTM_DASHBOARD_NO_CATEGORIES'); ?></p>
                <?php else : ?>
                    <div>
                        <?php foreach ($this->categories as $category) : ?>
                            <div class="llm-cat">
                                <div>
                                    <?php echo str_repeat('<span class="text-muted">–&nbsp;</span>', max(0, (int) $category->level - 1)); ?>
                                    <a href="<?php echo $link('view=linklists&filter[category_id]=' . (int) $category->id . '&filter[published]='); ?>">
                                        <?php echo $e($category->title); ?>
                                    </a>
                                    <span class="small text-muted">(<?php echo Text::sprintf('COM_LINKLISTM_DASHBOARD_CATEGORY_ID', (int) $category->id); ?>)</span>
                                    <?php if ((int) $category->published !== 1) : ?>
                                        <span class="badge bg-secondary"><?php echo Text::_('JUNPUBLISHED'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="llm-cat-count small">
                                    <?php echo Text::plural('COM_LINKLISTM_DASHBOARD_N_LINKS', $category->published_links); ?>
                                    <?php if ($category->unpublished_links > 0) : ?>
                                        <span class="text-muted">· <?php echo Text::plural('COM_LINKLISTM_DASHBOARD_N_UNPUBLISHED', $category->unpublished_links); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="small text-muted mt-3 mb-0">
                        <?php echo Text::sprintf('COM_LINKLISTM_DASHBOARD_PLUGIN_HINT', '<code>{linklistm cat=' . (int) $this->categories[0]->id . '}</code>'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h5"><?php echo Text::_('COM_LINKLISTM_DASHBOARD_TODOS'); ?></h2>

                <?php if ($this->todos === []) : ?>
                    <p class="mb-0 text-muted"><?php echo Text::_('COM_LINKLISTM_DASHBOARD_NOTHING_TODO'); ?></p>
                <?php endif; ?>

                <?php foreach ($this->todos as $todo) :
                    $class = 'llm-todo-yellow';
                    $url   = '';

                    switch ($todo['type']) {
                        case 'security_update':
                            $class = 'llm-todo-danger';
                            $text  = Text::sprintf('COM_LINKLISTM_TODO_SECURITY_UPDATE', $todo['update']['version'], Text::_('COM_LINKLISTM_SECURITY_LEVEL_' . $todo['update']['security']));
                            $url   = $installerLink('update');
                            break;
                        case 'update':
                            $text = Text::sprintf('COM_LINKLISTM_TODO_UPDATE', $todo['update']['version']);
                            $url  = $installerLink('update');
                            break;
                        case 'no_categories':
                            $text = Text::_('COM_LINKLISTM_TODO_NO_CATEGORIES');
                            $url  = Route::_('index.php?option=com_categories&extension=com_linklistm');
                            break;
                        case 'unpublished':
                            $text = Text::plural('COM_LINKLISTM_TODO_UNPUBLISHED', $todo['count']);
                            $url  = $link('view=linklists&filter[published]=0&filter[category_id]=');
                            break;
                        case 'empty_category':
                            $text = Text::sprintf('COM_LINKLISTM_TODO_EMPTY_CATEGORY', $todo['category']->title);
                            $url  = Route::_('index.php?option=com_categories&task=category.edit&id=' . (int) $todo['category']->id . '&extension=com_linklistm');
                            break;
                        case 'no_menu_item':
                            $text = Text::_('COM_LINKLISTM_TODO_NO_MENU_ITEM');
                            $url  = Route::_('index.php?option=com_menus&view=items&menutype=');
                            break;
                        default:
                            continue 2;
                    }
                    ?>
                    <div class="llm-todo <?php echo $class; ?>">
                        <span class="icon-warning llm-todo-icon" aria-hidden="true"></span>
                        <?php echo $url !== '' ? '<a href="' . $url . '">' . $e($text) . '</a>' : $e($text); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h5"><?php echo Text::_('COM_LINKLISTM_DASHBOARD_INFO'); ?></h2>
                <p class="mb-1"><strong><?php echo Text::_('COM_LINKLISTM'); ?></strong> (<?php echo $e($info['name']); ?>)</p>
                <p class="mb-1"><?php echo Text::sprintf('COM_LINKLISTM_DASHBOARD_VERSION', $e($info['version']), $e($info['date'])); ?></p>
                <p class="mb-1"><?php echo $e($info['author']); ?>
                    <?php if ($info['url'] !== '') : ?>
                        · <a href="<?php echo $e($info['url']); ?>" target="_blank" rel="noopener"><?php echo $e(preg_replace('#^https?://(www\.)?#', '', $info['url'])); ?></a>
                    <?php endif; ?>
                </p>
                <p class="small text-muted"><?php echo $e($info['license']); ?></p>
                <?php if ($info['email'] !== '') : ?>
                    <a class="btn btn-sm btn-outline-secondary" href="mailto:<?php echo $e($info['email']); ?>?subject=<?php echo rawurlencode('com_linklistm ' . $info['version']); ?>">
                        <?php echo Text::_('COM_LINKLISTM_DASHBOARD_REPORT_MAIL'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h5"><?php echo Text::_('COM_LINKLISTM_DASHBOARD_CHECKS'); ?></h2>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($this->checks as $check) :
                        $icon      = $check['ok'] ? 'icon-check llm-check-ok' : (!empty($check['danger']) ? 'icon-warning llm-check-danger' : 'icon-warning llm-check-warn');
                        $text      = $e($check['args'] === [] ? Text::_($check['key']) : Text::sprintf($check['key'], ...$check['args']));
                        $checkLink = isset($check['link']) ? $installerLink($check['link']) : '';
                        ?>
                        <li class="mb-1">
                            <span class="<?php echo $icon; ?>" aria-hidden="true"></span>
                            <?php echo $checkLink !== '' ? '<a href="' . $checkLink . '">' . $text . '</a>' : $text; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h5"><?php echo Text::_('COM_LINKLISTM_DASHBOARD_CHANGES'); ?></h2>
                <?php foreach (array_slice($this->changelog, 0, 3) as $entry) : ?>
                    <p class="mb-1"><strong><?php echo $e($entry['version']); ?></strong> <span class="small text-muted"><?php echo $e($entry['date']); ?></span></p>
                    <ul class="small">
                        <?php foreach ($entry['changes'] as $change) : ?>
                            <li><?php echo $e($change); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
                <?php if (count($this->changelog) > 3) : ?>
                    <details>
                        <summary class="small"><?php echo Text::_('COM_LINKLISTM_DASHBOARD_ALL_CHANGES'); ?></summary>
                        <?php foreach (array_slice($this->changelog, 3) as $entry) : ?>
                            <p class="mb-1 mt-2"><strong><?php echo $e($entry['version']); ?></strong> <span class="small text-muted"><?php echo $e($entry['date']); ?></span></p>
                            <ul class="small">
                                <?php foreach ($entry['changes'] as $change) : ?>
                                    <li><?php echo $e($change); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endforeach; ?>
                    </details>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
