<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

defined('_JEXEC') or die;

use Yireo\Model\ModelItems;

class UsergroupsModel extends ModelItems
{
    public function __construct($config = [])
    {
        $config['table_alias'] = 'usergroup';
        parent::__construct($config);
    }
}
