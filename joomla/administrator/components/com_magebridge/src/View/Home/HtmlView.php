<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Home;

defined('_JEXEC') or die;

use Yireo\View\ViewHome;

class HtmlView extends ViewHome
{
    protected array $icons = [];

    protected array $urls = [];

    public function display($tpl = null)
    {
        $icons   = [];
        $icons[] = $this->icon('config', 'COM_MAGEBRIDGE_VIEW_CONFIG', 'config.png');
        $icons[] = $this->icon('stores', 'COM_MAGEBRIDGE_VIEW_STORES', 'store.png');
        $icons[] = $this->icon('products', 'COM_MAGEBRIDGE_VIEW_PRODUCTS', 'product.png');
        $icons[] = $this->icon('users', 'COM_MAGEBRIDGE_VIEW_USERS', 'user.png');
        $icons[] = $this->icon('check', 'COM_MAGEBRIDGE_VIEW_CHECK', 'cpanel.png');
        $icons[] = $this->icon('logs', 'COM_MAGEBRIDGE_VIEW_LOGS', 'info.png');
        $icons[] = $this->icon('cache', 'COM_MAGEBRIDGE_CLEAN_CACHE', 'trash.png');
        $icons[] = $this->icon('magento', 'COM_MAGEBRIDGE_MAGENTO_BACKEND', 'magento.png', null, '_blank');
        $this->icons = $icons;

        $urls             = [];
        $urls['twitter']  = 'http://twitter.com/yireo';
        $urls['facebook'] = 'http://www.facebook.com/yireo';
        $this->urls       = $urls;

        parent::display($tpl);
    }
}
