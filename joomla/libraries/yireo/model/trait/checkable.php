<?php

/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory;

/**
 * Yireo Model Trait: Checkable - allows models to have checkout behaviour
 *
 * @package Yireo
 */
trait YireoModelTraitCheckable
{
    /**
     * Tests if an item is checked out
     *
     * @param int $uid
     *
     * @return bool
     */
    public function isCheckedOut($uid = 0)
    {
        if ($this->allowCheckout() == false) {
            return false;
        }

        if ($this->getData() == false) {
            return false;
        }

        $data = $this->getData();

        if ($uid) {
            return ($data->checked_out && $data->checked_out != $uid);
        }

        return $data->checked_out;
    }

    /**
     * Method to checkin/unlock the table
     *
     * @param null
     *
     * @return bool
     */
    public function checkin()
    {
        if ($this->allowCheckout() == false) {
            return true;
        }

        $id = $this->getId();

        if (!$id) {
            return false;
        }

        try {
            $this->table->checkin($id);
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return true;
    }

    /**
     * Method to checkout/lock the table
     *
     * @param int $userId
     *
     * @return bool
     */
    public function checkout($userId = null)
    {
        if ($this->allowCheckout() == false) {
            return true;
        }

        $id = $this->getId();

        if (!$id) {
            return false;
        }

        // Make sure we have a user id to checkout the item with
        if (is_null($userId)) {
            $userId = $this->getCheckoutUserId();
        }

        // Lets get to it and checkout the thing...
        try {
            $this->table->checkout($userId, $id);
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function allowCheckout()
    {
        if ($this->getConfig('checkout') == false) {
            return false;
        }

        if ($this->table->hasField('checked_out') == false) {
            return false;
        }

        return true;
    }

    /**
     * @return mixed
     */
    protected function getCheckoutUserId()
    {
        if ($this->user) {
            return $this->user->get('id');
        }

        return 0;
    }
}
