<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Yireo\Model\ModelItems;

class ProductsModel extends ModelItems
{
    public function __construct($config = [])
    {
        $this->setConfig('checkout', false);
        $this->setConfig('search_fields', ['label', 'sku']);

        $config['table_alias'] = 'product';
        parent::__construct($config);

        $connector = $this->getFilter('connector');

        if (!empty($connector)) {
            $db = $this->getDatabase();
            $this->addWhere($this->getConfig('table_alias') . '.`connector` = ' . $db->quote($connector));
        }

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

        // Add search filter - use hardcoded search_fields since getConfig() may not be reliable here
        $search = $this->getFilter('search');
        if (!empty($search)) {
            $this->queryConfig['filter_search'] = $search;
            $this->queryConfig['search_fields'] = ['label', 'sku'];
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

    /**
     * Convert params JSON string to Registry object for each item.
     */
    protected function onDataLoad(array $data): array
    {
        foreach ($data as $item) {
            if (isset($item->params)) {
                if (is_string($item->params)) {
                    $item->params = new Registry($item->params);
                } elseif (!$item->params instanceof Registry) {
                    $item->params = new Registry();
                }
            } else {
                $item->params = new Registry();
            }
        }

        return $data;
    }
}
