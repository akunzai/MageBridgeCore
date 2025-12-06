<?php

/**
 * MageBridge.
 *
 * @author Yireo
 * @copyright Copyright 2016
 * @license Open Source License
 *
 * @link https://www.yireo.com
 */

/*
 * MageBridge model for outputting widgets
 */
class Yireo_MageBridge_Model_Widget
{
    /*
     * Output a certain widgets HTML
     *
     * @access public
     * @param int $widget_id
     * @param array $arguments
     * @return string
     */
    public function getOutput($widget_id, $arguments = [])
    {
        /** @var Mage_Widget_Model_Widget_Instance $widget */
        $widget = Mage::getModel('widget/widget_instance')->load($widget_id);

        $parameters = $widget->getWidgetParameters();
        if (!isset($parameters['template'])) {
            $templates = $widget->getWidgetTemplates();
            if (isset($templates['default']['value'])) {
                $parameters['template'] = $templates['default']['value'];
            }
        }

        $htmlParameters = [];
        foreach ($parameters as $name => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $htmlParameters[] = $name.'="'.$value.'"';
        }

        $html = '{{widget type="'.$widget->getType().'" '.implode(' ', $htmlParameters).'}}';
        $response = null;
        /** @var Mage_Widget_Model_Template_Filter $processor */
        $processor = Mage::getModel('widget/template_filter');
        if ($processor) {
            $response = $processor->filter($html);
        }

        // Check for non-string output
        if (empty($response) || !is_string($response)) {
            return null;
        }

        // Prepare the response for the bridge
        /** @var Yireo_MageBridge_Helper_Encryption $encryptionHelper */
        $encryptionHelper = Mage::helper('magebridge/encryption');
        $response = $encryptionHelper->base64_encode($response);
        return $response;
    }
}
