<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;
use MageBridge\Module\MageBridgeNewsletter\Site\Helper\NewsletterHelper;

/** @var Joomla\Registry\Registry $params */

// Read the parameters
$layout = $params->get('layout', 'default');

// Get the helper from the service container
/** @var \MageBridge\Module\MageBridgeNewsletter\Site\Helper\NewsletterHelper $helper */
$helper = Factory::getContainer()->get(NewsletterHelper::class);

// Call the helper
$block = $helper::build($params);

// Get the current user
$user = Factory::getApplication()->getIdentity();

// Set the form URL
$form_url = UrlHelper::route('newsletter/subscriber/new');
$redirect_url = UrlHelper::route(UrlHelper::getRequest());
$redirect_url = EncryptionHelper::base64_encode($redirect_url);

// Include the layout-file
require(ModuleHelper::getLayoutPath('mod_magebridge_newsletter', $layout));
