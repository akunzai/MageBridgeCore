<?php

declare(strict_types=1);

namespace Yireo\Model\Trait;

use Joomla\CMS\Application\CMSApplication;

defined('_JEXEC') or die();

/**
 * Yireo Model Trait: Limitable - allows models to have limit functionality.
 */
trait Limitable
{
    /**
     * Method to initialize the limit parameter.
     *
     * @param int|null $limit
     */
    public function initLimit($limit = null)
    {
        if (empty($limit)) {
            $limit = $this->getFilter('list_limit');
        }

        if (empty($limit)) {
            /** @var CMSApplication $app */
            $app = $this->app;
            $defaultLimit = (string) $app->get('list_limit');
            $limit = $app->getUserStateFromRequest($this->getFilterName('limit'), 'list_limit', $defaultLimit, 'int');
        }

        $this->setState('limit', $limit);
    }

    /**
     * Method to initialize the limitstart parameter.
     *
     * @param int|null $limitStart
     */
    public function initLimitstart($limitStart = null)
    {
        if (is_numeric($limitStart) === false) {
            /** @var CMSApplication $app */
            $app = $this->app;
            if ($app->isClient('site')) {
                $limitStart = $app->input->getInt('limitstart', 0);
            } else {
                $limitStart = $app->getUserStateFromRequest($this->getFilterName('limitstart'), 'limitstart', '0', 'int');
            }
        }

        $this->setState('limitstart', $limitStart);
    }

    /**
     * Reset the limit parameters.
     */
    public function resetLimits()
    {
        $this->setState('limitstart', 0);
        $this->setState('limit', 0);
    }

    /**
     * Method to set whether the query should use LIMIT or not.
     *
     * @param bool $bool
     */
    public function setLimitQuery($bool)
    {
        $this->setConfig('limit_query', (bool) $bool);
    }
}
