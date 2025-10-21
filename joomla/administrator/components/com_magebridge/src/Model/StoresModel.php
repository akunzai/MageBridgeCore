<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

defined('_JEXEC') or die;

use Yireo\Model\ModelItems;

class StoresModel extends ModelItems
{
    public function __construct($config = [])
    {
        $this->setConfig('search_fields', ['description']);

        $config['table_alias'] = 'store';
        parent::__construct($config);
    }
}
