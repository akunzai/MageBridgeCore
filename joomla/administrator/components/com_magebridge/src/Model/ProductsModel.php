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
