<?php

\defined('_JEXEC') or die;

?>

<?php
$renderCategory = function ($category) use (&$renderCategory) : void {
    $indentClass = 'ms-' . min($category['level'] * 2, 5);
    ?>

    <div class="list-group-item <?php echo $indentClass; ?> <?php echo $category['level'] > 1 ? 'list-group-item-secondary fw-semibold' : 'active'; ?>">
        <?php echo $this->escape($category['title']); ?>
    </div>

    <?php foreach ($category['items'] as $item) : ?>

    <div class="list-group-item <?php echo $indentClass; ?>">

        <div class="fw-semibold mb-1">
            <span class="icon-globe me-1" aria-hidden="true"></span>
            <a href="<?php echo $this->escape($item->url); ?>">
                <?php echo $this->escape($item->title); ?>
            </a>
        </div>

        <?php if ($this->showDescription) : ?>
            <div class="small text-muted ps-4">
                <?php echo $item->description; ?>
            </div>
        <?php endif; ?>

    </div>    <?php endforeach; ?>

    <?php foreach ($category['children'] as $child) : ?>
        <?php $renderCategory($child); ?>
    <?php endforeach; ?>

    <?php
};
?>

<?php foreach ($this->rootCategories as $category) : ?>

    <div class="list-group mb-4">
        <?php $renderCategory($category); ?>
    </div>

<?php endforeach; ?>