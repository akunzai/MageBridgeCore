<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

defined('_JEXEC') or die;

use Yireo\Model\ModelItem;

class ProductModel extends ModelItem
{
    protected string $_orderby_title = 'label';

    public function __construct($config = [])
    {
        $config['table_alias'] = 'product';
        parent::__construct($config);
    }

    public function store($data)
    {
        if (empty($data['label']) && !empty($data['sku'])) {
            $data['label'] = $data['sku'];
        }

        $data['connector'] ??= '';
        $data['connector_value'] ??= '';

        return parent::store($data);
    }
}
