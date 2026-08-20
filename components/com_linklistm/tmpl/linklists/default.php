<?php

\defined('_JEXEC') or die;

?>

<?php foreach ($this->itemsByCategory as $categoryItems) : ?>

    <?php $level = (int) $categoryItems[0]->level; ?>
    <?php $indent = $level > 1 ? ' ms-' . min($level, 5) : ''; ?>

    <div class="list-group mb-4<?php echo $indent; ?>">

        <div class="list-group-item active">
            <?php echo $this->escape($categoryItems[0]->category_title); ?>
        </div>

        <?php foreach ($categoryItems as $item) : ?>

            <div class="list-group-item">

                <h2 class="h5 mb-1">
                    <a href="<?php echo $this->escape($item->url); ?>">
                        <?php echo $this->escape($item->title); ?>
                    </a>
                </h2>

                <?php if ($this->showDescription) : ?>
                    <div class="mt-2">
                        <?php echo $item->description; ?>
                    </div>
                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    </div>

<?php endforeach; ?>