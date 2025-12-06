<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Bridge;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use MageBridge\Component\MageBridge\Site\Helper\TemplateHelper;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;

final class Breadcrumbs extends Segment
{
    public static function getInstance($name = null)
    {
        return parent::getInstance(self::class);
    }

    public function getResponseData()
    {
        return $this->register->getData('breadcrumbs');
    }

    public function setBreadcrumbs(): bool
    {
        static $set = false;

        if ($set === true) {
            return true;
        }

        $set = true;

        if ($this->app->input->getCmd('view') !== 'root') {
            return true;
        }

        $pathway = $this->app->getPathway();
        $data    = $this->getResponseData();

        if (!is_array($data)) {
            $data = [];
        }

        if (TemplateHelper::isCartPage()) {
            $pathway->addItem(Text::_('COM_MAGEBRIDGE_SHOPPING_CART'), UrlHelper::route('checkout/cart'));
        } elseif (TemplateHelper::isCheckoutPage()) {
            $pathway->addItem(Text::_('COM_MAGEBRIDGE_SHOPPING_CART'), UrlHelper::route('checkout/cart'));
            $pathway->addItem(Text::_('COM_MAGEBRIDGE_CHECKOUT'), UrlHelper::route('checkout'));
        }

        array_shift($data);

        if (empty($data)) {
            return true;
        }

        $pathwayItems = [];

        foreach ($pathway->getPathway() as $pathwayItem) {
            if (!preg_match('/^(http|https):/', $pathwayItem->link)) {
                $pathwayItem->link = preg_replace('/\/$/', '', Uri::root()) . Route::_($pathwayItem->link);
            }

            $pathwayItems[] = $pathwayItem;
        }

        $rootItem = UrlHelper::getRootItem();

        if ($rootItem !== false) {
            array_pop($pathwayItems);

            $rootPathwayItem       = new \stdClass();
            $rootPathwayItem->name = Text::_($rootItem->name ?? $rootItem->title ?? '');
            $rootPathwayItem->link = preg_replace('/\/$/', '', Uri::base()) . Route::_($rootItem->link);

            $homeMatch = false;

            foreach ($pathwayItems as $pathwayItem) {
                if ($pathwayItem->link === $rootPathwayItem->link || str_contains($pathwayItem->link, $rootPathwayItem->link)) {
                    $homeMatch = true;
                    break;
                }
            }

            if (!empty($rootItem->home) && $rootItem->home == 1) {
                $homeMatch = true;
            }

            if ($homeMatch === false) {
                $pathwayItems[] = $rootPathwayItem;
            }
        } else {
            array_shift($data);
        }

        foreach ($data as $item) {
            if (empty($item['link'])) {
                $item['link'] = Uri::current();
            }

            if (!empty($pathwayItems)) {
                $match = false;

                foreach ($pathwayItems as $pathwayItem) {
                    if (empty($pathwayItem) || !is_object($pathwayItem)) {
                        continue;
                    }

                    if ($pathwayItem->link === $item['link']) {
                        $match = true;
                    }
                }

                if ($match === true) {
                    continue;
                }
            }

            $pathwayItem          = new \stdClass();
            $pathwayItem->name    = Text::_($item['label']);
            $pathwayItem->link    = $item['link'];
            $pathwayItem->magento = 1;
            $pathwayItems[]       = $pathwayItem;
        }

        $pathway->setPathway($pathwayItems);

        return true;
    }
}
