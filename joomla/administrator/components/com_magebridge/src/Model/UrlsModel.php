<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

defined('_JEXEC') or die;

use Yireo\Model\ModelItems;

class UrlsModel extends ModelItems
{
    public function __construct($config = [])
    {
        $this->setConfig('search_fields', ['source', 'destination']);
        $config['table_alias'] = 'url';
        parent::__construct($config);
    }
}
