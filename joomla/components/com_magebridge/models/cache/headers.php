<?php

/**
 * Joomla! component MageBridge.
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license GNU Public License
 *
 * @link https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Bridge caching class.
 */
class MageBridgeModelCacheHeaders extends MageBridgeModelCache
{
    /**
     * Constructor.
     *
     * @param $request string
     * @param @cache_time int
     */
    public function __construct($request = null, $cache_time = null)
    {
        parent::__construct('headers', $request, $cache_time);
    }

    /**
     * Method to store the data to cache.
     *
     * @param mixed $data
     *
     * @return bool
     */
    public function store($data)
    {
        $data = serialize($data);
        return parent::store($data);
    }

    /**
     * Method to load data from cache.
     *
     * @return mixed
     */
    public function load()
    {
        $data = parent::load();
        return unserialize($data);
    }
}
