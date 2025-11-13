<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

defined('_JEXEC') or die;

use Yireo\Model\ModelItem;

class UsergroupModel extends ModelItem
{
    protected string $_orderby_title = 'description';

    public function __construct($config = [])
    {
        $config['table_alias'] = 'usergroup';
        parent::__construct($config);
    }
}
