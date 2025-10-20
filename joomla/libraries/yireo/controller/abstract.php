<?php

/**
 * Joomla! Yireo Library.
 *
 * @author    Yireo (http://www.yireo.com/)
 * @license   GNU Public License
 *
 * @link      https://www.yireo.com/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import the loader
require_once dirname(__FILE__) . '/../loader.php';

/**
 * Yireo Abstract Controller.
 */
class YireoAbstractController extends JControllerLegacy
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->handleLegacy();

        // Call the parent constructor
        parent::__construct();
    }

    protected function handleLegacy()
    {
    }
}
