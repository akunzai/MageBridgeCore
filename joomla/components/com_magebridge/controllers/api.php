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

class MageBridgeControllerApi extends YireoAbstractController
{
    public function run()
    {
        // Parse the POST-request
        $post = Factory::getApplication()->input->post->getArray();
        $data = [];

        foreach ($post as $name => $value) {
            $value = json_decode($value);
            $data[$name] = $value;
        }

        if ($this->authenticate($data) == false) {
            return false;
        }

        if (is_array($data) && !empty($data)) {
            $this->dispatch($data);
        }
    }

    protected function authenticate()
    {
        if (isset($data['meta']['api_user']) && isset($data['meta']['api_key'])) {
            // @todo: Perform authentication of these data
            return true;
        }

        return false;
    }

    /**
     * Method to dispatch the incoming request to various parts of the bridge.
     */
    protected function dispatch($data)
    {
        foreach ($data as $index => $segment) {
            switch ($index) {
                case 'authenticate':
                    // @todo: $segment['data'] = MageBridgeModelUser->authenticate($segment['arguments']);
                    break;

                case 'event':
                    // @todo: $segment['data'] = MageBridgeModelUser->authenticate($segment['arguments']);
                    break;

                case 'log':
                    // @todo: $segment['data'] = MageBridgeModelUser->authenticate($segment['arguments']);
                    break;
            }
        }
    }
}
