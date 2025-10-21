<?php

declare(strict_types=1);

namespace Yireo\View;

defined('_JEXEC') or die();

/**
 * Item View class.
 */
class ViewItem extends View
{
    /**
     * Main display method.
     *
     * @param string $tpl
     */
    public function display($tpl = null)
    {
        // Automatically fetch item
        $this->fetchItem();

        parent::display($tpl);
    }
}
