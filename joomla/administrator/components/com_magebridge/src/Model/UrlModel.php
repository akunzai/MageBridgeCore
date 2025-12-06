<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

defined('_JEXEC') or die;

use Yireo\Model\ModelItem;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;

class UrlModel extends ModelItem
{
    protected string $_orderby_title = 'source';

    public function __construct($config = [])
    {
        $config['table_alias'] = 'url';
        parent::__construct($config);
    }

    public function store($data)
    {
        $result = parent::store($data);

        if ((int) ($data['published'] ?? 0) === 1) {
            ConfigModel::getSingleton()->saveValue('load_urls', 1);
        }

        return $result;
    }
}
