<?php

declare(strict_types=1);

namespace Yireo\Model\Trait;

use Joomla\CMS\Application\CMSApplication;

defined('_JEXEC') or die();

/**
 * Yireo Model Trait: Identifiable - allows models to have an ID.
 */
trait Filterable
{
    /**
     * Method to get a filter from the user-state.
     *
     * @param string $filter
     * @param string $default
     * @param string $type
     * @param string $option
     *
     * @return string|null
     */
    public function getFilter($filter = '', $default = '', $type = 'cmd', $option = '')
    {
        if ($this->getConfig('allow_filter', true) == false) {
            return null;
        }

        /** @var CMSApplication $app */
        $app = $this->app;
        $value = $app->getUserStateFromRequest($this->getFilterName($filter, $option), 'filter_' . $filter, $default, $type);

        return $value;
    }

    /**
     * Get the current filter name.
     *
     * @param string $filter
     * @param string|null $option
     *
     * @return string
     */
    public function getFilterName($filter, $option = null)
    {
        if (empty($option)) {
            $option = $this->getConfig('option_id');
        }

        return $option . 'filter_' . $filter;
    }

    /**
     * Method to set whether filtering is allowed.
     *
     * @param bool $allowFilter
     */
    public function setAllowFilter($allowFilter)
    {
        $this->setConfig('allow_filter', (bool) $allowFilter);
    }
}
