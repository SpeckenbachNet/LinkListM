<?php

namespace SpeckenbachNet\Component\LinkListM\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Builds the display structure for the link list: one block per top category,
 * each with a flat, ordered list of sub sections (no nested indentation).
 *
 * Pure logic without Joomla dependencies, so it can be tested in isolation.
 */
class CategoryTree
{
    /**
     * @param   object[]  $categories   Published, accessible categories (id, parent_id, title, lft, rgt), any order
     * @param   object[]  $items        Links (with catid), already in the desired order within a category
     * @param   int[]     $selectedIds  Categories chosen in the menu item; empty = all
     * @param   bool      $includeSub   Include subcategories of the selected categories
     *
     * @return  array  List of blocks: ['id', 'title', 'items', 'sections' => [['id', 'title', 'depth', 'path', 'items']]]
     */
    public static function build(array $categories, array $items, array $selectedIds, bool $includeSub): array
    {
        usort($categories, fn($a, $b) => (int) $a->lft <=> (int) $b->lft);

        // A category is only available if its whole parent chain is available (published + accessible)
        $available = [];

        foreach ($categories as $cat) {
            $parentId = (int) $cat->parent_id;

            if ($parentId <= 1 || isset($available[$parentId])) {
                $available[(int) $cat->id] = $cat;
            }
        }

        // Determine which categories are visible
        $selectedIds = array_values(array_filter(array_map('intval', $selectedIds)));
        $visible     = [];

        if ($selectedIds === []) {
            $visible = $available;
        } else {
            foreach ($selectedIds as $selId) {
                if (!isset($available[$selId])) {
                    continue;
                }

                $sel = $available[$selId];
                $visible[$selId] = $sel;

                if ($includeSub) {
                    foreach ($available as $id => $cat) {
                        if ((int) $cat->lft > (int) $sel->lft && (int) $cat->rgt < (int) $sel->rgt) {
                            $visible[$id] = $cat;
                        }
                    }
                }
            }

            // Restore tree order after merging several selections
            uasort($visible, fn($a, $b) => (int) $a->lft <=> (int) $b->lft);
        }

        // Group links by category
        $itemsByCat = [];

        foreach ($items as $item) {
            $catId = (int) $item->catid;

            if (isset($visible[$catId])) {
                $itemsByCat[$catId][] = $item;
            }
        }

        // Nearest visible ancestor of each visible category (0 = none, i.e. it is a block)
        $displayParent = [];

        foreach ($visible as $id => $cat) {
            $parentId = (int) $cat->parent_id;

            while ($parentId > 1 && !isset($visible[$parentId])) {
                $parentId = isset($available[$parentId]) ? (int) $available[$parentId]->parent_id : 0;
            }

            $displayParent[$id] = $parentId > 1 ? $parentId : 0;
        }

        // A category has content if it or any visible descendant has links (walk bottom-up)
        $hasContent = [];

        foreach (array_reverse($visible, true) as $id => $cat) {
            if (!empty($itemsByCat[$id])) {
                $hasContent[$id] = true;
            }

            if (!empty($hasContent[$id]) && $displayParent[$id]) {
                $hasContent[$displayParent[$id]] = true;
            }
        }

        // Build blocks with flat section lists
        $blocks = [];
        $blockIndex = [];

        foreach ($visible as $id => $cat) {
            if (empty($hasContent[$id])) {
                continue;
            }

            if (!$displayParent[$id]) {
                $blockIndex[$id] = \count($blocks);
                $blocks[] = [
                    'id'       => $id,
                    'title'    => (string) $cat->title,
                    'items'    => $itemsByCat[$id] ?? [],
                    'sections' => [],
                ];

                continue;
            }

            // Collect visible ancestors up to the block
            $chain    = [];
            $parentId = $displayParent[$id];

            while ($parentId && $displayParent[$parentId]) {
                array_unshift($chain, (string) $visible[$parentId]->title);
                $parentId = $displayParent[$parentId];
            }

            $blocks[$blockIndex[$parentId]]['sections'][] = [
                'id'    => $id,
                'title' => (string) $cat->title,
                'depth' => \count($chain) + 1,
                'path'  => $chain,
                'items' => $itemsByCat[$id] ?? [],
            ];
        }

        return $blocks;
    }
}
