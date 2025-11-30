<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Language\Text;
use Yireo\Model\ModelItem;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;

class StoreModel extends ModelItem
{
    protected bool $_single = true;

    public function __construct($config = [])
    {
        $config['table_alias'] = 'store';
        parent::__construct($config);
    }

    public function delete($cid = [])
    {
        if (is_array($cid) && in_array(0, $cid, true)) {
            $data = [
                'storegroup' => '',
                'storeview'  => '',
            ];

            ConfigModel::getSingleton()->store($data);
        }

        return parent::delete($cid);
    }

    public function store($data)
    {
        if (empty($data['store'])) {
            throw new Exception(Text::_('COM_MAGEBRIDGE_MODEL_STORE_NO_STORE_SELECTED'));
        }

        $values       = explode(':', $data['store']);
        $data['type'] = ($values[0] === 'g') ? 'storegroup' : 'storeview';
        $data['name'] = $values[1] ?? '';
        $data['title'] = $values[2] ?? '';
        unset($data['store']);

        if (!empty($data['default'])) {
            $this->storeDefault($data['type'], $data['name']);

            return true;
        }

        if (empty($data['name']) || empty($data['title'])) {
            throw new Exception(Text::_('COM_MAGEBRIDGE_MODEL_STORE_INVALID_STORE'));
        }

        if (empty($data['label'])) {
            $data['label'] = $data['title'];
        }

        $result = parent::store($data);

        if ($result && ((int) ($data['published'] ?? 0) === 1)) {
            // @phpstan-ignore-next-line
            \MageBridge::getConfig()->saveValue('load_stores', 1);
        }

        return $result;
    }

    private function storeDefault(string $type, string $name): void
    {
        if ($type === 'storeview') {
            $post = [
                'storegroup' => '',
                'storeview'  => $name,
            ];
        } else {
            $post = [
                'storegroup' => $name,
                'storeview'  => '',
            ];
        }

        ConfigModel::getSingleton()->store($post);
    }
}
