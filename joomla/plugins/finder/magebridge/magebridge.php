<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Yireo\Helper\PathHelper;

// Load the base adapter.
require_once PathHelper::getAdministratorPath() . '/components/com_finder/helpers/indexer/adapter.php';

// For backward compatibility, create an alias to the namespaced class
class_alias('MageBridge\\Plugin\\Finder\\MageBridge\\Extension\\MageBridge', 'PlgFinderMageBridge');
