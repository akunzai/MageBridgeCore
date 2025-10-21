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

// Handy variables
$request = MageBridge\Component\MageBridge\Site\Helper\UrlHelper::getRequest() ?? '';
$bridge = MageBridge\Component\MageBridge\Site\Model\BridgeModel::getInstance();
$page_layout = MageBridge\Component\MageBridge\Site\Helper\TemplateHelper::getRootTemplate() ?? '';

if (!isset($html) || !is_string($html)) {
    /** @var string $html */
    $html = '';
}

/**
 * Developers note: Do NOT edit the contents of this file directly.
 * Instead, create a override of this file by copying it to:
 *
 * "templates/YOUR_TEMPLATE/html/com_magebridge/fixes.php
 */

// FIX: Magento refers from opcheckout.js to these specific HTML-classes, but currently we do not care
if (strstr($request, 'checkout/onepage') && $bridge->getBlock('checkout.progress') == '') {
    $html .= '<!-- Begin Checkout Progress Fix -->';
    $html .= '<div class="col-right" style="display:none;">';
    $html .= '<div class="one-page-checkout-progress"></div>';
    $html .= '<div id="checkout-progress-wrapper"></div>';
    $html .= '<div id="col-right-opcheckout"></div>';
    $html .= '</div>';
    $html .= '<!-- End Checkout Progress Fix -->';
}

// FIX: Make sure that when "page/one-column.phtml" is used, we set the Joomla! variable "tmpl=component"
if ($page_layout == 'page/one-column.phtml') {
    $application = Factory::getApplication();
    $application->getInput()->set('tmpl', 'component');
}

// Developers note: Make sure the $html variable still contains your data

// End
