<?php

declare(strict_types=1);

namespace Yireo\Controller;

defined('_JEXEC') or die();

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Yireo Abstract Controller.
 */
class AbstractController extends BaseController
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
