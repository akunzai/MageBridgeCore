<?php

declare(strict_types=1);

namespace Yireo\View;

defined('_JEXEC') or die();

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Yireo\Helper\Helper;

/**
 * Home View class.
 */
class ViewHome extends View
{
    /**
     * Identifier of the library-view.
     */
    protected $_viewParent = 'home';

    /**
     * @var bool
     */
    protected $backend_feed;

    /**
     * @var string
     */
    public $current_version;

    /**
     * Main constructor method.
     *
     * @param $config array
     */
    public function __construct($config = [])
    {
        $this->loadToolbar = false;

        // Call the parent constructor
        parent::__construct($config);

        // Load bootstrap
        Helper::bootstrap();

        // Initialize the toolbar
        $option = $this->getConfig('option');
        if (file_exists(JPATH_ADMINISTRATOR . '/components/' . $option . '/config.xml')) {
            if ($this->user->authorise('core.admin')) {
                ToolbarHelper::preferences($option, 600, 800);
            }
        }

        // Add the checks
        $this->runChecks();
    }

    /**
     * Main display method.
     *
     * @param string $tpl
     */
    public function display($tpl = null)
    {
        // Variables
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $document = $app->getDocument();

        // Add additional CSS
        $wa = $document->getWebAssetManager();
        $wa->registerAndUseStyle('google-fonts.opensans', 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700');
        $wa->registerAndUseStyle('font-awesome', 'https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css');

        // Get the current version
        $this->current_version = Helper::getCurrentVersion();

        parent::display($tpl);
    }

    /**
     * Helper-method to construct a specific icon.
     *
     * @param string $view
     * @param string $text
     * @param string $image
     * @param string|null $folder
     * @param string|null $target
     *
     * @return array<string, mixed>
     */
    public function icon($view, $text, $image, $folder = null, $target = null)
    {
        $image = 'icon-48-' . $image;

        if (empty($folder)) {
            $folder = '../media/' . $this->getConfig('option') . '/images/';
        }

        if (!file_exists(JPATH_ADMINISTRATOR . '/' . $folder . '/' . $image)) {
            $folder = '/templates/' . $this->app->getTemplate() . '/images/header/';
        }

        $icon           = [];
        $icon['link']   = Route::_('index.php?option=' . $this->getConfig('option') . '&view=' . $view);
        $icon['text']   = Text::_($text);
        $icon['target'] = $target;
        $icon['icon']   = '<img src="' . $folder . $image . '" title="' . $icon['text'] . '" alt="' . $icon['text'] . '" />';

        return $icon;
    }

    /**
     * Helper-method to set the page title.
     *
     * @param string $title
     * @param string $class
     */
    public function setTitle($title = null, $class = 'logo')
    {
        $component_title = Helper::getData('title') ?? 'MageBridge';
        $title           = Text::_('LIB_YIREO_VIEW_HOME');
        $icon = file_exists(JPATH_SITE . '/media/' . $this->getConfig('option') . '/images/' . $class . '.png') ? $class : 'generic.png';
        ToolbarHelper::title($component_title . ': ' . $title, $icon);
    }

    /**
     * Helper-method to add checks to the homepage.
     */
    public function runChecks()
    {
    }
}
