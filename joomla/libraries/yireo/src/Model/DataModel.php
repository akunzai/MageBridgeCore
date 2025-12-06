<?php

declare(strict_types=1);

namespace Yireo\Model;

defined('_JEXEC') or die();

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Yireo\Model\Data\Query;
use Yireo\Model\Data\Querytext;
use Yireo\Model\Trait\Debuggable;
use Yireo\Model\Trait\Table;

/**
 * Yireo Data Model.
 */
class DataModel extends CommonModel
{
    /**
     * Trait to implement debugging behaviour.
     */
    use Debuggable;

    /**
     * Trait to implement table behaviour.
     */
    use Table;

    protected array|object $data = [];

    /**
     * @var Registry|null
     */
    protected $params;

    /**
     * @var Query|Querytext
     */
    protected $query;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        // Call the parent constructor
        parent::__construct($config);

        $this->setConfig('skip_table', false);
        $this->setConfig('table_prefix_auto', true);

        $this->setTablePrefix();
        $this->table = $this->getTable($this->getConfig('table_alias'));
        $this->initQuery();
    }

    /**
     * @return $this
     */
    public function initQuery()
    {
        if (method_exists($this, 'buildQuery') || method_exists($this, 'buildQueryWhere')) {
            $this->query = new Querytext($this->table, $this->getConfig('table_alias'));
            $this->query->setModel($this);

            return $this;
        }

        $this->query = new Query($this->table, $this->getConfig('table_alias'));
        $this->query->setModel($this);

        return $this;
    }

    /**
     * @return Query|Querytext
     */
    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Method to override a default user-state value.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return bool
     *
     * @todo Code smell
     */
    public function overrideUserState($key, $value)
    {
        $this->$key = $value;

        return true;
    }

    /**
     * @param mixed $name
     * @param mixed $value
     */
    public function setData($name, $value = null)
    {
        if (is_array($name) && empty($value)) {
            $this->data = $name;

            return;
        }

        $this->data[$name] = $value;
    }

    /**
     * Method to get data.
     *
     * @param bool $forceNew
     *
     * @return array
     */
    public function getData($forceNew = false)
    {
        return $this->data;
    }

    /**
     * @return bool|mixed
     */
    public function getDataByName($name = null)
    {
        if (empty($this->data[$name])) {
            return false;
        }

        return $this->data[$name];
    }

    /**
     * Method to fetch database-results.
     *
     * @param string|\Joomla\Database\QueryInterface $query
     * @param string $type : object|objectList|result
     *
     * @return mixed
     */
    public function getDbResult($query, $type = 'object')
    {
        if ($this->getConfig('cache') == true) {
            $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
            $cache = $cacheControllerFactory->createCacheController('callback', ['defaultgroup' => 'lib_yireo_model']);
            $rs    = $cache->get([$this, '_getDbResult'], $query, $type);
        } else {
            $rs = $this->_getDbResult($query, $type);
        }

        return $rs;
    }

    /**
     * Method to fetch database-results.
     *
     * @param string|\Joomla\Database\QueryInterface $query
     * @param string $type : object|objectList|result
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function _getDbResult($query, $type = 'object')
    {
        // Set the query in the database-object
        $db = $this->getDatabase();
        $db->setQuery($query);

        // Print the query if debugging is enabled
        if ($this->allowDebug()) {
            $this->app->enqueueMessage($this->getDbDebug(), 'debug');
        }

        // Fetch the database-result
        if ($type == 'objectList') {
            $rs = $db->loadObjectList();
        } elseif ($type == 'result') {
            $rs = $db->loadResult();
        } else {
            $rs = $db->loadObject();
        }

        // Return the result
        return $rs;
    }
}
