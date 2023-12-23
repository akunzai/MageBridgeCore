<?php

/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   0.6.1
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Path;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Include the loader
require_once dirname(__FILE__) . '/../loader.php';

/**
 * Yireo Common View
 *
 * @package Yireo
 */
class YireoCommonView extends YireoAbstractView
{
    /**
     * Trait to implement ID behavior
     */
    use YireoModelTraitConfigurable;

    /**
     * Array of template-paths to look for layout-files
     */
    protected $templatePaths = [];

    /**
     * Flag to determine whether this view is a single-view
     */
    protected $_single = null;

    /**
     * Identifier of the library-view
     */
    protected $_viewParent = 'default';

    /**
     * Default task
     */
    protected $_task = null;

    /**
     * @var \Joomla\Database\DatabaseDriver
     */
    protected $db;

    /**
     * @var \Joomla\CMS\Application\CMSApplication
     */
    protected $app;

    /**
     * @var \Joomla\CMS\Input\Input
     */
    protected $input;

    /**
     * @var \Joomla\CMS\Document\HtmlDocument
     */
    protected $doc;

    /**
     * @var \Joomla\CMS\User\User
     */
    protected $user;

    /**
     * @var \Joomla\CMS\Uri\Uri
     */
    protected $uri;

    /**
     * Main constructor method
     *
     * @subpackage Yireo
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        // Call the parent constructor
        parent::__construct($config);

        // Import use full variables from Factory
        $this->db          = Factory::getDbo();
        $this->uri         = Uri::getInstance();
        $this->doc         = Factory::getDocument();
        $this->user        = version_compare(JVERSION, '4.0.0', '<')
            ? Factory::getUser()
            : Factory::getApplication()->getIdentity();
        $this->app         = Factory::getApplication();
        $this->input       = $this->app->input;

        // Create the namespace-variables
        $this->setConfig('view', (!empty($config['name'])) ? $config['name'] : $this->input->getCmd('view', 'default'));
        $this->setConfig('option', (!empty($config['option'])) ? $config['option'] : $this->input->getCmd('option'));

        $this->_name = $this->getConfig('view');
        $option_id   = $this->getConfig('option') . '_' . $this->getConfig('view') . '_';

        if ($this->app->isClient('site')) {
            $option_id .= $this->input->getInt('Itemid') . '_';
        }

        $this->setConfig('option_id', $option_id);

        // Load additional language-files
        YireoHelper::loadLanguageFile();
    }

    /**
     * Helper method to determine whether this is a new entry or not
     *
     * @return bool
     */
    public function isEdit()
    {
        $cid = $this->input->get('cid', [0], '', 'array');

        if (!empty($cid) && $cid > 0) {
            return true;
        }

        $id = $this->input->getInt('id');

        if (!empty($id) && $id > 0) {
            return true;
        }

        return false;
    }

    /**
     * Helper-method to set the page title
     *
     * @subpackage Yireo
     *
     * @param string $title
     * @param string $class
     *
     * @return null
     */
    protected function setTitle($title = null, $class = 'logo')
    {
        $component_title = YireoHelper::getData('title');

        if (empty($title)) {
            $views = YireoHelper::getData('views');

            if (!empty($views)) {
                foreach ($views as $view => $view_title) {
                    if ($this->getConfig('view') == $view) {
                        $title = Text::_($this->input->getCmd('option') . '_VIEW_' . $view);
                        break;
                    }
                }
            }
        }

        if ($this->_single) {
            $pretext = ($this->isEdit()) ? Text::_('LIB_YIREO_VIEW_EDIT') : Text::_('LIB_YIREO_VIEW_NEW');
            $title   = $pretext . ' ' . $title;
        }

        $icon = file_exists(JPATH_SITE . '/media/' . $this->getConfig('option') . '/images/' . $class . '.png') ? $class : 'generic.png';
        ToolbarHelper::title($component_title . ': ' . $title, $icon);

        return;
    }

    /**
     * Helper-method to set the page title
     *
     * @subpackage Yireo
     *
     * @return null
     */
    public function setMenu()
    {
        $menuitems = YireoHelper::getData('menu', $this->getConfig('option'));

        if (!empty($menuitems)) {
            foreach ($menuitems as $view => $title) {
                if (strstr($view, '|')) {
                    $v      = explode('|', $view);
                    $view   = $v[0];
                    $layout = $v[1];
                } else {
                    $layout = null;
                }

                $titleLabel = strtoupper($this->getConfig('option')) . '_VIEW_' . strtoupper($title);

                if (is_dir(JPATH_ADMINISTRATOR . '/components/' . $this->getConfig('option') . '/views/' . $view)) {
                    if ($this->getConfig('view') == $view && $this->input->getCmd('layout') == $layout) {
                        $active = true;
                    } else {
                        if ($this->getConfig('view') == $view && empty($layout)) {
                            $active = true;
                        } else {
                            $active = false;
                        }
                    }

                    $url = 'index.php?option=' . $this->getConfig('option') . '&view=' . $view;

                    if ($layout) {
                        $url .= '&layout=' . $layout;
                    }
                    JHtmlSidebar::addEntry(Text::_($titleLabel), $url, $active);
                } else {
                    if (preg_match('/option=/', $view)) {
                        JHtmlSidebar::addEntry(Text::_($titleLabel), 'index.php?' . $view, false);
                    }
                }
            }
        }
    }

    /**
     * Add a specific CSS-stylesheet to this page
     *
     * @subpackage Yireo
     *
     * @param string $stylesheet
     *
     * @return void
     */
    public function addCss($stylesheet)
    {
        $prefix   = ($this->app->isClient('site')) ? 'site-' : 'backend-';
        $template = $this->app->getTemplate();

        if (file_exists(JPATH_SITE . '/templates/' . $template . '/css/' . $this->getConfig('option') . '/' . $prefix . $stylesheet)) {
            $this->doc->addStyleSheet(Uri::root() . 'templates/' . $template . '/css/' . $this->getConfig('option') . '/' . $prefix . $stylesheet);

            return;
        }

        if (file_exists(JPATH_SITE . '/media/' . $this->getConfig('option') . '/css/' . $prefix . $stylesheet)) {
            $this->doc->addStyleSheet(Uri::root() . 'media/' . $this->getConfig('option') . '/css/' . $prefix . $stylesheet);

            return;
        }

        if (file_exists(JPATH_SITE . '/templates/' . $template . '/css/' . $this->getConfig('option') . '/' . $stylesheet)) {
            $this->doc->addStyleSheet(Uri::root() . 'templates/' . $template . '/css/' . $this->getConfig('option') . '/' . $stylesheet);

            return;
        }

        if (file_exists(JPATH_SITE . '/media/' . $this->getConfig('option') . '/css/' . $stylesheet)) {
            $this->doc->addStyleSheet(Uri::root() . 'media/' . $this->getConfig('option') . '/css/' . $stylesheet);

            return;
        }

        if (file_exists(JPATH_SITE . '/media/lib_yireo/css/' . $stylesheet)) {
            $this->doc->addStyleSheet(Uri::root() . 'media/lib_yireo/css/' . $stylesheet);

            return;
        }
    }

    /**
     * Add a specific JavaScript-script to this page
     *
     * @subpackage Yireo
     *
     * @param string $script
     *
     * @return null
     */
    public function addJs($script)
    {
        $prefix   = ($this->app->isClient('site')) ? 'site-' : 'backend-';
        $template = $this->app->getTemplate();

        if (file_exists(JPATH_SITE . '/templates/' . $template . '/js/' . $this->getConfig('option') . '/' . $prefix . $script)) {
            $this->doc->addScript(Uri::root() . 'templates/' . $template . '/js/' . $this->getConfig('option') . '/' . $prefix . $script);

            return;
        }

        if (file_exists(JPATH_SITE . '/media/' . $this->getConfig('option') . '/js/' . $prefix . $script)) {
            $this->doc->addScript(Uri::root() . 'media/' . $this->getConfig('option') . '/js/' . $prefix . $script);

            return;
        }

        if (file_exists(JPATH_SITE . '/templates/' . $template . '/js/' . $this->getConfig('option') . '/' . $script)) {
            $this->doc->addScript(Uri::root() . 'templates/' . $template . '/js/' . $this->getConfig('option') . '/' . $script);

            return;
        }

        if (file_exists(JPATH_SITE . '/media/' . $this->getConfig('option') . '/js/' . $script)) {
            $this->doc->addScript(Uri::root() . 'media/' . $this->getConfig('option') . '/js/' . $script);

            return;
        }

        if (file_exists(JPATH_SITE . '/media/lib_yireo/js/' . $script)) {
            $this->doc->addScript(Uri::root() . 'media/lib_yireo/js/' . $script);

            return;
        }
    }

    /**
     * Add a folder to the template-search path
     *
     * @subpackage Yireo
     *
     * @param string  $path
     * @param bool $first
     *
     * @return bool
     */
    protected function addNewTemplatePath($path, $first = true)
    {
        // If this path is non-existent, skip it
        if (!is_dir($path)) {
            return false;
        }

        // If this path is already included, skip it
        if (in_array($path, $this->templatePaths)) {
            return false;
        }

        // Add this path to the beginning of the array
        if ($first) {
            array_unshift($this->templatePaths, $path);

            return true;
        }

        // Add this path to the end of the array
        $this->templatePaths[] = $path;

        return true;
    }

    /**
     * An override of the original JView-function to allow template files across multiple layouts
     *
     * @param string $file
     * @param array  $variables
     *
     * @return string
     */
    public function loadTemplate($file = null, $variables = [])
    {
        $option = $this->getConfig('option');
        $view   = $this->getConfig('view');

        // Construct the paths where to locate a specific template
        if ($this->app->isClient('site') == false) {
            // Reset the template-paths
            $this->templatePaths = [];

            // Local layout
            $this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $option . '/views/' . $view . '/tmpl', true);

            // Library defaults
            $this->addNewTemplatePath(JPATH_LIBRARIES . '/yireo/view/' . $view, false);
            $this->addNewTemplatePath(JPATH_LIBRARIES . '/yireo/view/' . $this->_viewParent, false);

            $this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $option . '/lib/view/' . $this->_viewParent, false);
            $this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $option . '/libraries/view/' . $this->_viewParent, false);
        } else {
            $template = $this->app->getTemplate();

            // Local layout
            $this->addNewTemplatePath(JPATH_SITE . '/components/' . $option . '/views/' . $view . '/tmpl', true);

            // Template override
            $this->addNewTemplatePath(JPATH_THEMES . '/' . $template . '/html/lib_yireo/' . $view, true);
            $this->addNewTemplatePath(JPATH_THEMES . '/' . $template . '/html/' . $option . '/' . $view, true);

            // Library defaults
            $this->addNewTemplatePath(JPATH_THEMES . '/' . $template . '/html/lib_yireo/' . $this->_viewParent, true);
            $this->addNewTemplatePath(JPATH_LIBRARIES . '/yireo/view/' . $this->_viewParent, false);
            $this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $option . '/lib/view/' . $this->_viewParent, false);
            $this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $option . '/libraries/view/' . $this->_viewParent, false);
        }

        // Default file
        if (empty($file)) {
            $file = 'default.php';
        }

        $templatePaths = $this->templatePaths;

        // Deal with any subfolders (not recommended, but still possible)
        if (strstr($file, '/')) {
            $fileParts = explode('/', $file);
            $file      = array_pop($fileParts);

            foreach ($templatePaths as $templatePathIndex => $templatePath) {
                foreach ($fileParts as $filePart) {
                    $templatePaths[$templatePathIndex] = $templatePath . '/' . $filePart;
                }
            }
        }

        // Find the template-file
        if (!preg_match('/\.php$/', $file)) {
            $file = $file . '.php';
        }

        $template = Path::find($templatePaths, $file);

        // If this template is empty, try to use alternatives
        if (empty($template) && $file == 'default.php') {
            $file     = 'form.php';
            $template = Path::find($templatePaths, $file);
        }

        $output = null;

        if ($template != false) {
            // Include the variables here
            if (!empty($variables)) {
                foreach ($variables as $name => $value) {
                    $$name = $value;
                }
            }

            // Unset so as not to introduce into template scope
            unset($file);

            // Never allow a 'this' property
            if (isset($this->{'this'})) {
                unset($this->{'this'});
            }

            // Unset variables
            unset($variables);
            unset($name);
            unset($value);

            // Start capturing output into a buffer
            ob_start();
            include $template;

            // Done with the requested template; get the buffer and clear it.
            $output = ob_get_contents();
            ob_end_clean();

            return $output;
        }

        if ($this->getConfig('debug')) {
            throw new RuntimeException('Template file can not be located: ' . $this->_viewParent . '/' . $file);
        }

        return '';
    }
}
