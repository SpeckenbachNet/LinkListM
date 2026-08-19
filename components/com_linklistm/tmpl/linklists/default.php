<?php

\defined('_JEXEC') or die;

?>

<?php foreach ($this->items as $item) : ?>

    <div class="linklist-item mb-4 pb-3 border-bottom">

        <h2 class="h5 mb-1">
            <a href="<?php echo $this->escape($item->url); ?>">
                <?php echo $this->escape($item->title); ?>
            </a>
        </h2>

        <div class="small text-muted mb-1">
            <?php echo $this->escape($item->category_title); ?>
        </div>

        <?php if ($this->showDescription) : ?>
            <div class="linklist-description">
                <?php echo $item->description; ?>
            </div>
        <?php endif; ?>

    </div>

<?php endforeach; ?>