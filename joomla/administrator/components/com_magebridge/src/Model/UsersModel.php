<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

defined('_JEXEC') or die;

use Yireo\Model\ModelItems;

class UsersModel extends ModelItems
{
    public function __construct($config = [])
    {
        $config['table_alias'] = 'user';
        parent::__construct($config);
    }

    public function getData($forceNew = false)
    {
        // Apply limit and ordering before fetching data
        $this->queryConfig['limit.count'] = $this->getLimit();
        $this->queryConfig['limit.start'] = $this->getLimitstart();

        // Add search filter
        $search = $this->getSearch();
        if (!empty($search)) {
            $this->addSearch($search);
        }

        // Add ordering
        $this->addOrdering();

        return parent::getData($forceNew);
    }
}
