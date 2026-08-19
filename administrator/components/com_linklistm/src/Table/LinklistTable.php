<?php

namespace SpeckenbachNet\Component\LinkListM\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Factory;

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
        // Basis-Prüfungen der Elternklasse
        if (!parent::check()) {
            return false;
        }

        // Datum für Erstellung setzen, wenn es ein NEUER Eintrag ist ($this->id ist 0 oder leer)
        if (empty($this->id)) {
            if (empty($this->created)) {
                $this->created = Factory::getDate()->toSql();
            }

            // Falls Sie auch ein Feld created_by in der Datenbank haben:
            if (empty($this->created_by)) {
                $this->created_by = Factory::getApplication()->getIdentity()->id;
            }
        } else {
            // Bei bestehenden Einträgen optional das Geändert-am-Datum pflegen
            if (property_exists($this, 'modified') && empty($this->modified)) {
                $this->modified = Factory::getDate()->toSql();
            }
            if (property_exists($this, 'modified_by') && empty($this->modified_by)) {
                $this->modified_by = Factory::getApplication()->getIdentity()->id;
            }
        }
        return true;
    }
}