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

use Joomla\CMS\Factory;

// No direct access
defined('_JEXEC') or die('Restricted access');

// Include the parent class
require_once JPATH_COMPONENT . '/view.php';

/**
 * HTML View class.
 *
 * @static
 */
class MageBridgeViewContent extends MageBridgeView
{
    /**
     * @var string
     */
    protected $logout_url;

    /**
     * @var mixed
     */
    protected $params;

    /**
     * Method to display the requested view.
     */
    public function display($tpl = null)
    {
        $application = Factory::getApplication();
        $params = $application->getParams();

        // Set the request based upon the choosen layout
        switch ($this->getLayout()) {
            case 'logout':
                $intermediate_page = $params->get('intermediate_page');
                if ($intermediate_page != 1) {
                    $this->setRequest('customer/account/logout');
                } else {
                    $this->logout_url = MageBridgeUrlHelper::route('customer/account/logout');
                }
                break;

            default:
                $this->setRequest(MageBridgeUrlHelper::getLayoutUrl($this->getLayout()));
                break;
        }

        // Set which block to display
        $this->setBlock('content');

        // Assign the parameters to this template
        $this->params = $params;

        parent::display($tpl);
    }
}
