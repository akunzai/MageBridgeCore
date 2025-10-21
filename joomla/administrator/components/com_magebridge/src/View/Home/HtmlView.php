<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Home;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Yireo\View\ViewHome;

class HtmlView extends ViewHome
{
    /** @var array<int, array<string, mixed>> */
    public array $icons = [];

    /** @var array<string, string> */
    public array $urls = [];

    public function display($tpl = null)
    {
        $icons   = [];
        $icons[] = $this->icon('config', 'COM_MAGEBRIDGE_VIEW_CONFIG', 'config.png');
        $icons[] = $this->icon('stores', 'COM_MAGEBRIDGE_VIEW_STORES', 'store.png');
        $icons[] = $this->icon('products', 'COM_MAGEBRIDGE_VIEW_PRODUCTS', 'product.png');
        $icons[] = $this->icon('users', 'COM_MAGEBRIDGE_VIEW_USERS', 'user.png');
        $icons[] = $this->icon('check', 'COM_MAGEBRIDGE_VIEW_CHECK', 'cpanel.png');
        $icons[] = $this->icon('logs', 'COM_MAGEBRIDGE_VIEW_LOGS', 'info.png');
        $icons[] = $this->taskIcon('cache', 'COM_MAGEBRIDGE_CLEAN_CACHE', 'trash.png');
        $icons[] = $this->icon('magento', 'COM_MAGEBRIDGE_MAGENTO_BACKEND', 'magento.png', null, '_blank');
        $this->icons = $icons;

        $urls           = [];
        $urls['GitHub'] = 'https://github.com/akunzai/MageBridgeCore';
        $this->urls     = $urls;

        parent::display($tpl);
    }

    /**
     * Helper method to construct an icon that triggers a task (not a view).
     *
     * @param string $task The task name
     * @param string $text The language key for the text
     * @param string $image The image filename
     * @param string|null $folder The folder path
     *
     * @return array<string, mixed>
     */
    protected function taskIcon(string $task, string $text, string $image, ?string $folder = null): array
    {
        $image = 'icon-48-' . $image;

        if (empty($folder)) {
            $folder = '../media/' . $this->getConfig('option') . '/images/';
        }

        if (!file_exists(JPATH_ADMINISTRATOR . '/' . $folder . '/' . $image)) {
            $folder = '/templates/' . $this->app->getTemplate() . '/images/header/';
        }

        $textValue = Text::_($text);
        $link = Route::_('index.php?option=' . $this->getConfig('option') . '&task=' . $task . '&' . Session::getFormToken() . '=1');

        return [
            'link'   => $link,
            'text'   => $textValue,
            'target' => null,
            'icon'   => '<img src="' . $folder . $image . '" title="' . $textValue . '" alt="' . $textValue . '" />',
        ];
    }

    /**
     * @inheritDoc
     */
    public function setTitle($title = null, $class = 'logo')
    {
        $title = Text::_('COM_MAGEBRIDGE_VIEW_HOME_CONTROL_PANEL');
        ToolbarHelper::title('MageBridge: ' . $title, $class);
    }
}
