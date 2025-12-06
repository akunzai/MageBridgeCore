<?php

declare(strict_types=1);

namespace Yireo\Model\Trait;

defined('_JEXEC') or die();

/**
 * Yireo Model Trait: Identifiable - allows models to have an ID.
 */
trait Debuggable
{
    /**
     * @return bool
     */
    protected function allowDebug()
    {
        // Enable debugging
        // @phpstan-ignore-next-line
        if ($this->params->get('debug', 0) == 1) {
            return true;
        }

        if ($this->getConfig('debug')) {
            return true;
        }

        return false;
    }

    /**
     * Method to get a debug-message of the latest query.
     *
     * @return string
     */
    public function getDbDebug()
    {
        $db = $this->db;
        $query = (string) $db->getQuery();

        return '<pre>' . str_replace('#__', $db->getPrefix(), $query) . '</pre>';
    }
}
