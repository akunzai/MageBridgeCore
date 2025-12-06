<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\View\Root;

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use MageBridge\Component\MageBridge\Site\Helper\TemplateHelper;
use MageBridge\Component\MageBridge\Site\Library\MageBridge;
use MageBridge\Component\MageBridge\Site\View\BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public array $content_class = [];

    public function display($tpl = null)
    {
        $this->setBlock('content');

        $block = $this->build();

        if (TemplateHelper::isProductPage()) {
            $tpl = 'product';
        } elseif (TemplateHelper::isCategoryPage()) {
            $tpl = 'category';
        }

        $bridge = MageBridge::getBridge();
        $app = Factory::getApplication();

        if ($bridge->isAjax()) {
            echo $block;
            $app->close();
        }

        $mageConfig    = $bridge->getSessionData();
        $mageController = $mageConfig['controller'] ?? null;
        $mageAction     = $mageConfig['action'] ?? null;

        $contentClass = ['magebridge-content'];

        if (!empty($mageController)) {
            $contentClass[] = 'magebridge-' . $mageController;
        }

        if (!empty($mageController) && !empty($mageAction)) {
            $contentClass[] = 'magebridge-' . $mageController . '-' . $mageAction;
        }

        $this->content_class = $contentClass;

        parent::display($tpl);
    }
}
