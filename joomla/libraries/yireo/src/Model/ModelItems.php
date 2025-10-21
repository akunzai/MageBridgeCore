<?php

declare(strict_types=1);

namespace Yireo\Model;

defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Yireo\Model\Trait\Checkable;
use Yireo\Model\Trait\Filterable;
use Yireo\Model\Trait\Limitable;
use Yireo\Model\Trait\Paginable;

/**
 * Yireo Model
 * Parent class for models that use the full-blown MVC pattern.
 */
class ModelItems extends DataModel
{
    /**
     * Trait to implement checkout behaviour.
     */
    use Checkable;

    /**
     * Trait to implement pagination behaviour.
     */
    use Paginable;

    /**
     * Trait to implement filter behaviour.
     */
    use Filterable;

    /**
     * Trait to implement filter behaviour.
     */
    use Limitable;

    /**
     * @var array
     */
    protected $queryConfig = [];

    /**
     * Ordering field.
     *
     * @var string|null
     */
    protected $_ordering = null;

    /**
     * Search columns.
     *
     * @var array
     */
    protected $search = [];

    /**
     * List of fields to autoconvert into column-separated fields.
     *
     * @var array
     */
    protected $_columnFields = [];

    /**
     * Constructor.
     *
     * @param mixed $config
     */
    public function __construct($config = [])
    {
        // Handle a deprecated constructor call
        if (is_string($config)) {
            $tableAlias        = $config;
            $this->setConfig('table_alias', $tableAlias);
            $config            = ['table_alias' => $tableAlias];
        }

        // Call the parent constructor
        parent::__construct($config);

        $this->setConfig('skip_table', false);
        $this->setConfig('table_prefix_auto', true);
        $this->setConfig('limit_query', true);
        $this->setTablePrefix();
        $this->table = $this->getTable($this->getConfig('table_alias'));

        $this->initOrderBy();
        $this->initPlural();
        $this->initLimit();
        $this->initLimitstart();
    }

    /**
     * @return \Joomla\Registry\Registry
     */
    protected function initParams()
    {
        $this->params = ComponentHelper::getParams($this->getConfig('option'));

        return $this->params;
    }

    /**
     * Inititalize system variables.
     */
    protected function initPlural()
    {
        // Set the parameters for the frontend
        $this->initParams();
    }

    /**
     * Initialize the ordering.
     */
    protected function initOrderBy()
    {
        $defaultOrderBy = $this->table->getDefaultOrderBy();
        if (is_string($defaultOrderBy)) {
            $this->_ordering = $defaultOrderBy;
        }
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
        if (!empty($this->data) && $forceNew === false) {
            return $this->data;
        }

        // Build the query
        $queryConfig = $this->queryConfig;
        $query       = $this->query->setConfig($queryConfig)->build();

        // Get the data
        $data = $this->getDbResult($query, 'objectList');

        if (!empty($data)) {
            // Prepare the column-fields
            if (!empty($this->_columnFields)) {
                foreach ($data as $item) {
                    foreach ($this->_columnFields as $columnField) {
                        if (!empty($item->$columnField) && !is_array($item->$columnField)) {
                            $item->$columnField = explode('|', $item->$columnField);
                        }
                    }
                }
            }

            // Allow to modify the data
            if (method_exists($this, 'onDataLoad')) {
                $data = $this->onDataLoad($data);
            }

            // Set the metadata
            foreach ($data as $item) {
                $item->metadata = $this->getConfig();
            }

            $this->data = $data;
        } else {
            $this->data = [];
        }

        // Allow to modify the data afterwards
        if (method_exists($this, 'onDataLoadAfter')) {
            $this->data = $this->onDataLoadAfter($this->data);
        }

        return $this->data;
    }

    /**
     * Method to get the total number of items.
     *
     * @return int
     */
    public function getTotal()
    {
        if (!empty($this->total)) {
            return $this->total;
        }

        // Build the query without limit
        $queryConfig             = $this->queryConfig;
        $queryConfig['no_limit'] = true;
        $query                   = $this->query->setConfig($queryConfig)->build();

        // Convert the SELECT query to COUNT query
        // Use the original query as a subquery to handle complex queries with JOINs
        $db          = $this->getDatabase();
        $countQuery  = $db->getQuery(true);
        $countQuery->select('COUNT(*)');
        $countQuery->from('(' . (string) $query . ') AS subquery');

        // Get the total
        $this->total = (int) $this->getDbResult($countQuery, 'result');

        return $this->total;
    }

    // Note: getPagination() is inherited from the Paginable trait

    /**
     * Method to set the limit.
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->setState('limit', $limit);
    }

    /**
     * Method to set the limitstart.
     *
     * @param int $limitstart
     */
    public function setLimitstart($limitstart)
    {
        $this->setState('limitstart', $limitstart);
    }

    /**
     * Method to set the ordering.
     *
     * @param string $ordering
     */
    public function setOrdering($ordering)
    {
        $this->setState('ordering', $ordering);
    }

    /**
     * Method to set the direction.
     *
     * @param string $direction
     */
    public function setDirection($direction)
    {
        $this->setState('direction', $direction);
    }

    /**
     * Method to set the search.
     *
     * @param string $search
     */
    public function setSearch($search)
    {
        $this->setState('search', $search);
    }

    /**
     * Method to get the ordering.
     *
     * @return string
     */
    public function getOrdering()
    {
        return $this->getState('ordering', $this->_ordering);
    }

    /**
     * Method to get the direction.
     *
     * @return string
     */
    public function getDirection()
    {
        return $this->getState('direction', 'asc');
    }

    /**
     * Method to get the search.
     *
     * @return string
     */
    public function getSearch()
    {
        return $this->getState('search');
    }

    /**
     * Method to get the limit.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->getState('limit');
    }

    /**
     * Method to get the limitstart.
     *
     * @return int
     */
    public function getLimitstart()
    {
        return $this->getState('limitstart');
    }

    /**
     * Method to add a new WHERE argument.
     *
     * @param mixed $where WHERE statement in the form of an array ($name, $value) or string
     * @param string $type Type of WHERE statement. Either "is" or "like".
     *
     * @return $this
     */
    public function addWhere($where, $type = 'is')
    {
        $this->query->addWhere($where, $type);
        return $this;
    }

    /**
     * Method to add an extra query argument.
     *
     * @param string $extra
     */
    public function addExtra($extra = null)
    {
        return $this->query->addExtra($extra);
    }

    /**
     * Method to add a search filter.
     *
     * @param string $search
     * @param array $searchColumns
     */
    public function addSearch($search, $searchColumns = [])
    {
        if (!empty($searchColumns)) {
            $this->search = $searchColumns;
        }

        if (!empty($this->search) && !empty($search)) {
            $db = $this->getDatabase();
            $search = $db->quote('%' . $db->escape($search, true) . '%', false);

            $where = [];

            foreach ($this->search as $column) {
                $where[] = $db->quoteName($column) . ' LIKE ' . $search;
            }

            $this->query->addWhere('(' . implode(' OR ', $where) . ')', 'raw');
        }
    }

    /**
     * Method to add a state filter.
     *
     * @param string $state
     */
    public function addState($state = null)
    {
        if ($state === null) {
            $state = $this->getState('state');
        }

        if ($state !== null && $state !== '') {
            $this->query->addWhere('state', $state);
        }
    }

    /**
     * Method to add a published filter.
     *
     * @param string $published
     */
    public function addPublished($published = null)
    {
        if ($published === null) {
            $published = $this->getState('published');
        }

        if ($published !== null && $published !== '') {
            $this->query->addWhere('published', $published);
        }
    }

    /**
     * Method to add a category filter.
     *
     * @param string $category
     */
    public function addCategory($category = null)
    {
        if ($category === null) {
            $category = $this->getState('category');
        }

        if ($category !== null && $category !== '') {
            $this->query->addWhere('catid', $category);
        }
    }

    /**
     * Method to add an access filter.
     *
     * @param string $access
     */
    public function addAccess($access = null)
    {
        if ($access === null) {
            $access = $this->getState('access');
        }

        if ($access !== null && $access !== '') {
            $this->query->addWhere('access', $access);
        }
    }

    /**
     * Method to add a language filter.
     *
     * @param string $language
     */
    public function addLanguage($language = null)
    {
        if ($language === null) {
            $language = $this->getState('language');
        }

        if ($language !== null && $language !== '') {
            $this->query->addWhere('language', $language);
        }
    }

    /**
     * Method to add a user filter.
     *
     * @param string $user
     */
    public function addUser($user = null)
    {
        if ($user === null) {
            $user = $this->getState('user');
        }

        if ($user !== null && $user !== '') {
            $this->query->addWhere('created_by', $user);
        }
    }

    /**
     * Method to add ordering.
     *
     * @param string|null $ordering
     * @param string|null $direction
     */
    public function addOrdering($ordering = null, $direction = null)
    {
        if ($ordering === null) {
            $ordering = $this->getOrdering();
        }

        if ($direction === null) {
            $direction = $this->getDirection();
        }

        if (!empty($ordering)) {
            $orderBy = $ordering . ' ' . strtoupper($direction);
            $this->query->addOrderby($orderBy);
        }
    }

    /**
     * Method to add limit.
     *
     * @param int|null $limit
     * @param int|null $limitstart
     */
    public function addLimit($limit = null, $limitstart = null)
    {
        if ($limit === null) {
            $limit = $this->getLimit();
        }

        if ($limitstart === null) {
            $limitstart = $this->getLimitstart();
        }

        if (!empty($limit)) {
            $this->query->setConfig('limit.count', $limit);
            $this->query->setConfig('limit.start', $limitstart);
        }
    }

    /**
     * Method to get the primary key.
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->table->getKeyName();
    }

    /**
     * Method to get the state field.
     *
     * @return string|null
     */
    protected function getStateField()
    {
        $stateField = $this->table->getStateField();
        return is_string($stateField) ? $stateField : null;
    }
}
