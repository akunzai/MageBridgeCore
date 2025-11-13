<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\Database\DatabaseQuery;
use Yireo\Model\ModelItems;

class LogsModel extends ModelItems
{
    public function __construct($config = [])
    {
        $this->setConfig('checkout', false);
        $this->setConfig('search_fields', ['message', 'session', 'http_agent']);

        $config['table_alias'] = 'log';
        parent::__construct($config);
    }

    public function onBuildQuery(DatabaseQuery $query): DatabaseQuery
    {
        $db = $this->getDatabase();
        $origin = $this->getFilter('origin');

        if (!empty($origin)) {
            $query->where($this->getConfig('table_alias') . '.' . $db->quoteName('origin') . ' = ' . $db->quote($origin));
        }

        $remoteAddr = $this->getFilter('remote_addr');

        if (!empty($remoteAddr)) {
            $query->where($this->getConfig('table_alias') . '.' . $db->quoteName('remote_addr') . ' = ' . $db->quote($remoteAddr));
        }

        $type = $this->getFilter('type');

        if (!empty($type)) {
            $query->where($this->getConfig('table_alias') . '.' . $db->quoteName('type') . ' = ' . $db->quote($type));
        }

        return $query;
    }
}
