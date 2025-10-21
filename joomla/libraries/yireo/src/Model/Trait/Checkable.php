<?php

declare(strict_types=1);

namespace Yireo\Model\Trait;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Factory;
use RuntimeException;

/**
 * Yireo Model Trait: Checkable - allows models to have checkout behaviour.
 */
trait Checkable
{
    /**
     * Tests if an item is checked out.
     *
     * @param \stdClass $item The item to check
     *
     * @return bool
     */
    public function isCheckedOut($item)
    {
        if ($this->allowCheckout() == false) {
            return false;
        }

        // Get the checked_out field name
        $checkedOutField = $this->table->getColumnAlias('checked_out');

        // Check if the item has the checked_out property and if it's checked out by a different user
        if (property_exists($item, $checkedOutField) && $item->{$checkedOutField}) {
            $currentUserId = Factory::getApplication()->getIdentity()->id ?? 0;
            return $item->{$checkedOutField} != $currentUserId;
        }

        return false;
    }

    /**
     * Method to checkin/unlock the table.
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
     * Method to checkout/lock the table.
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
        // @phpstan-ignore-next-line
        if ($this->user) {
            // @phpstan-ignore-next-line
            return $this->user->get('id');
        }

        return 0;
    }
}
