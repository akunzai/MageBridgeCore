<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

defined('_JEXEC') or die;

use Yireo\Model\ModelItems;

class UrlsModel extends ModelItems
{
    public function __construct($config = [])
    {
        $config['table_alias'] = 'url';
        parent::__construct($config);

        // Initialize filters in constructor so they're available for getTotal() and getData()
        $this->initFilters();
    }

    /**
     * Initialize query config with filters.
     * Called from constructor to ensure filters are set before getTotal() or getData().
     */
    protected function initFilters(): void
    {
        // Add state filter
        $state = $this->getFilter('state');
        if (!empty($state)) {
            $this->queryConfig['filter_state'] = $state;
        }

        // Add search filter
        $search = $this->getFilter('search');
        if (!empty($search)) {
            $this->queryConfig['filter_search'] = $search;
            $this->queryConfig['search_fields'] = ['source', 'destination'];
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
}
