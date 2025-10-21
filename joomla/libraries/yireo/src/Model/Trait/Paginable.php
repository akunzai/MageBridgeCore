<?php

declare(strict_types=1);

namespace Yireo\Model\Trait;

defined('_JEXEC') or die();

use Joomla\CMS\Pagination\Pagination;

/**
 * Yireo Model Trait: Paginable - allows models to have pagination support.
 */
trait Paginable
{
    /**
     * Items total.
     *
     * @var int|null
     */
    protected $total = null;

    /**
     * Pagination object.
     *
     * @var \Joomla\CMS\Pagination\Pagination|null
     */
    protected $pagination = null;

    /**
     * Method to get the total number of records.
     *
     * @return int
     */
    public function getTotal()
    {
        if (!empty($this->total)) {
            return $this->total;
        }

        // The original database-query did NOT include a LIMIT statement
        if ($this->getConfig('limit_query') == false) {
            $data        = $this->getData();
            $this->total = count($data);

            return $this->total;
        }

        if (method_exists($this, 'buildQueryObject')) {
            /** @var Joomla\Database\DatabaseQuery */
            $query = $this->buildQueryObject();
            $query->select('COUNT(*) AS count');
            $query->setLimit(0);
            $query->order($this->getPrimaryKey());
            $data = $this->getDbResult($query, 'object');
            $data = $data->count;
        }

        if (method_exists($this, 'buildQuery')) {
            /** @var string $query */
            $query = $this->buildQuery();
            $query = preg_replace('/^(.*)FROM/sm', 'SELECT COUNT(*) FROM', $query);
            $query = preg_replace('/LIMIT(.*)$/', '', $query);
            $query = preg_replace('/ORDER\ BY(.*)$/m', '', $query);
            $data        = $this->getDbResult($query, 'result');
        }

        $this->total = (int) $data;

        return $this->total;
    }

    /**
     * Method to get a pagination object for the fetched records.
     *
     * @return Pagination
     */
    public function getPagination()
    {
        if (!empty($this->pagination)) {
            return $this->pagination;
        }

        // Make sure the data is loaded
        $this->getData();
        $this->getTotal();

        // Reset pagination if it does not make sense
        if ($this->getState('limitstart') > $this->getTotal()) {
            $this->setState('limitstart', 0);
            // setUserState is only available in CMSApplication (not in interface)
            if (method_exists($this->app, 'setUserState')) {
                $this->app->setUserState('limitstart', 0);
            }
            $this->getData(true);
        }

        // Build the pagination
        $this->pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));

        return $this->pagination;
    }
}
