<?php

namespace SpeckenbachNet\Component\LinkListM\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class LinklistTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        // Define table name, primary key column and database connection
        parent::__construct('#__linklistm_links', 'id', $db);
    }
}