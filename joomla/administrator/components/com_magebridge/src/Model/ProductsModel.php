<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

defined('_JEXEC') or die;

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
}
