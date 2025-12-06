<?php

declare(strict_types=1);

namespace Yireo\Form\Field;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\TextField;
use SimpleXMLElement;

/*
 * Form Field-class for showing a yes/no field
 */
class Text extends TextField
{
    /**
     * @param mixed $value
     * @param string|null $group
     *
     * @throws Exception
     *
     * @return bool
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $rt = parent::setup($element, $value, $group);

        $label = (string) $this->element['label'];

        if (empty($label)) {
            $option = Factory::getApplication()->getInput()->getCmd('option');
            $prefix = $option;

            if ($option == 'com_plugins') {
                $prefix = $this->form->getData()
                    ->get('name');
            }

            $label = strtoupper($prefix . '_' . $this->fieldname);
        }

        $this->element['label'] = $label;
        $this->element['description'] = $label . '_DESC';
        $this->description = $label . '_DESC';

        return $rt;
    }

    /*
     * Method to construct the HTML of this element
     *
     * @return string
     */
    protected function getInput()
    {
        $classes = [
            'inputbox',
        ];

        $this->class = $this->class . ' ' . implode(' ', $classes);

        return parent::getInput();
    }
}
