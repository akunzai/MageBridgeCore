<?php

declare(strict_types=1);

defined('_JEXEC') or die;

// Load the base adapter.
require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';

// For backward compatibility, create an alias to the namespaced class
class_alias('MageBridge\\Plugin\\Finder\\MageBridge\\Extension\\MageBridge', 'PlgFinderMageBridge');
