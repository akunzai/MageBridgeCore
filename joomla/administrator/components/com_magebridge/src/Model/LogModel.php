<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Yireo\Model\ModelItem;

class LogModel extends ModelItem
{
    public function __construct($config = [])
    {
        $config['table_alias'] = 'log';
        parent::__construct($config);
    }

    public function add(string $message, int $level = 0): bool
    {
        $data = [
            'message' => $message,
            'level'   => $level,
        ];

        return $this->store($data);
    }

    public function store($data)
    {
        $now = new Date('now');

        $data['remote_addr'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $data['http_agent']  = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $data['timestamp']   = $now->toSql();

        return parent::store($data);
    }
}
