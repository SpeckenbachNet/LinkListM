<?php

namespace SpeckenbachNet\Component\LinkListM\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use SpeckenbachNet\Component\LinkListM\Administrator\Helper\UrlHelper;

class LinklistTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        // Define table name, primary key column and database connection
        parent::__construct('#__linklistm_links', 'id', $db);

        // Map internal "published" to our actual column "state"
        $this->setColumnAlias('published', 'state');
    }

    public function check()
    {
        if (!parent::check()) {
            return false;
        }

        // Links entered without scheme ("www.example.com") would otherwise become relative links
        $this->url = UrlHelper::normalize((string) $this->url);

        // Empty dates must be stored as NULL
        foreach (['publish_up', 'publish_down', 'modified'] as $column) {
            if (empty($this->$column)) {
                $this->$column = null;
            }
        }

        if (empty($this->language)) {
            $this->language = '*';
        }

        $now    = Factory::getDate()->toSql();
        $userId = (int) Factory::getApplication()->getIdentity()->id;

        if (empty($this->id)) {
            // New entry
            if (empty($this->created)) {
                $this->created = $now;
            }

            if (empty($this->created_by)) {
                $this->created_by = $userId;
            }
        } else {
            // Existing entry
            $this->modified    = $now;
            $this->modified_by = $userId;
        }

        return true;
    }

    /**
     * Store NULL values too, otherwise cleared publish dates would not be saved.
     */
    public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
