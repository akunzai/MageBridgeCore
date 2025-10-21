<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Element;

defined('_JEXEC') or die;

class AjaxView extends HtmlView
{
    public function display($tpl = null)
    {
        $layoutType = $this->app->getInput()->getCmd('type');

        switch ($layoutType) {
            case 'product':
                $this->doProductLayout();
                break;

            case 'customer':
                $this->doCustomerLayout();
                break;

            case 'widget':
                $this->doWidgetLayout();
                break;

            default:
                $this->doCategoryLayout();
                break;
        }

        parent::display($layoutType);
    }

    protected function doProductLayout(): void
    {
        // Implementation for product layout
    }

    protected function doCustomerLayout(): void
    {
        // Implementation for customer layout
    }

    protected function doWidgetLayout(): void
    {
        // Implementation for widget layout
    }

    protected function doCategoryLayout(): void
    {
        // Implementation for category layout
    }
}
