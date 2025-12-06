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
        $this->initFilters();
    }

    /**
     * Initialize filters early so they are available for both getTotal() and getData().
     */
    protected function initFilters(): void
    {
        $search = $this->getFilter('search');
        if (!empty($search)) {
            $this->queryConfig['filter_search'] = $search;
            $this->queryConfig['search_fields'] = ['message', 'session', 'http_agent'];
        }
    }

    public function getData($forceNew = false)
    {
        // Apply limit and ordering before fetching data
        $this->queryConfig['limit.count'] = $this->getLimit();
        $this->queryConfig['limit.start'] = $this->getLimitstart();

        // Add ordering from filter parameters
        $ordering = $this->getFilter('order');
        $direction = $this->getFilter('order_Dir');

        if (!empty($ordering)) {
            $this->addOrdering($ordering, $direction ?: 'asc');
        } else {
            $this->addOrdering();
        }

        return parent::getData($forceNew);
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
