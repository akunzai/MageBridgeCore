<?php

declare(strict_types=1);

namespace Yireo\View;

defined('_JEXEC') or die();

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Path;
use Joomla\Input\Input;
use Yireo\Helper\Helper;
use Yireo\Model\Trait\Configurable;

/**
 * Yireo Common View.
 */
class CommonView extends AbstractView
{
    /**
     * Trait to implement ID behavior.
     */
    use Configurable;

    /**
     * Array of template-paths to look for layout-files.
     */
    protected $templatePaths = [];

    /**
     * Flag to determine whether this view is a single-view.
     */
    protected $_single = null;

    /**
     * Identifier of the library-view.
     */
    protected $_viewParent = 'default';

    /**
     * Default task.
     */
    protected $_task = null;

    protected DatabaseInterface $db;

    /**
     * @var CMSApplication
     */
    protected $app;

    /**
     * @var Input
     */
    protected $input;

    /**
     * @var HtmlDocument
     */
    protected $doc;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Uri
     */
    protected $uri;

    /**
     * Main constructor method.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        // Call the parent constructor
        parent::__construct($config);

        // Import use full variables from Factory
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $this->db          = Factory::getContainer()->get(DatabaseInterface::class);
        $this->uri         = Uri::getInstance();
        /** @phpstan-ignore-next-line */
        $this->doc         = $app->getDocument();
        $this->user        = $app->getIdentity();
        $this->app         = $app;
        $this->input       = $this->app->getInput();

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
        Helper::loadLanguageFile();
    }

    /**
     * Helper method to determine whether this is a new entry or not.
     *
     * @return bool
     */
    public function isEdit()
    {
        $cid = $this->input->get('cid', [0], 'array');

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
     * Helper-method to set the page title.
     *
     * @param string $title
     * @param string $class
     */
    protected function setTitle($title = null, $class = 'logo')
    {
        $component_title = Helper::getData('title') ?? 'MageBridge';

        if (empty($title)) {
            $views = Helper::getData('views');
            $view = $this->getConfig('view');

            if (!empty($views)) {
                foreach ($views as $v => $view_title) {
                    if ($view == $v) {
                        $title = Text::_($this->input->getCmd('option') . '_VIEW_' . $v);
                        break;
                    }
                }
            }

            // Fallback: try to get title from language string directly
            if (empty($title) && !empty($view)) {
                $langKey = strtoupper($this->input->getCmd('option') . '_VIEW_' . $view);
                $title = Text::_($langKey);
                // If translation not found, use view name as fallback
                if ($title === $langKey) {
                    $title = ucfirst($view);
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
     * Helper-method to set the page title.
     */
    public function setMenu()
    {
        $menuitems = Helper::getData('menu', $this->getConfig('option'));

        if (empty($menuitems)) {
            return;
        }

        foreach ($menuitems as $view => $title) {
            $layout = null;

            if (strpos($view, '|') !== false) {
                [$view, $layout] = explode('|', $view, 2);
            }

            $option     = $this->getConfig('option');
            $titleLabel = strtoupper($option) . '_VIEW_' . strtoupper($title);

            $adminComponentPath = JPATH_ADMINISTRATOR . '/components/' . $option;
            $hasView            = is_dir($adminComponentPath . '/tmpl/' . $view)
                || is_dir($adminComponentPath . '/views/' . $view);

            $url = 'index.php?option=' . $option . '&view=' . $view;

            if (!empty($layout)) {
                $url .= '&layout=' . $layout;
            }

            $currentLayout = $this->input->getCmd('layout');
            $isActive      = $this->getConfig('view') === $view && ($layout === null || $currentLayout === $layout);

            if ($hasView) {
                $buttonName = $isActive ? 'active-link' : 'link';
                ToolbarHelper::link($url, $titleLabel, $buttonName);
            }
        }
    }

    /**
     * Add a specific CSS-stylesheet to this page.
     *
     * @param string $stylesheet
     * @param string $path Optional path prefix (deprecated, ignored)
     */
    public function addCss($stylesheet, $path = '')
    {
        $wa       = $this->doc->getWebAssetManager();
        $prefix   = ($this->app->isClient('site')) ? 'site-' : 'backend-';
        $template = $this->app->getTemplate();

        if (file_exists(JPATH_SITE . '/templates/' . $template . '/css/' . $this->getConfig('option') . '/' . $prefix . $stylesheet)) {
            $url = Uri::root() . 'templates/' . $template . '/css/' . $this->getConfig('option') . '/' . $prefix . $stylesheet;
            $wa->registerAndUseStyle('yireo-' . md5($url), $url);

            return;
        }

        if (file_exists(JPATH_SITE . '/media/' . $this->getConfig('option') . '/css/' . $prefix . $stylesheet)) {
            $url = Uri::root() . 'media/' . $this->getConfig('option') . '/css/' . $prefix . $stylesheet;
            $wa->registerAndUseStyle('yireo-' . md5($url), $url);

            return;
        }

        if (file_exists(JPATH_SITE . '/templates/' . $template . '/css/' . $this->getConfig('option') . '/' . $stylesheet)) {
            $url = Uri::root() . 'templates/' . $template . '/css/' . $this->getConfig('option') . '/' . $stylesheet;
            $wa->registerAndUseStyle('yireo-' . md5($url), $url);

            return;
        }

        if (file_exists(JPATH_SITE . '/media/' . $this->getConfig('option') . '/css/' . $stylesheet)) {
            $url = Uri::root() . 'media/' . $this->getConfig('option') . '/css/' . $stylesheet;
            $wa->registerAndUseStyle('yireo-' . md5($url), $url);

            return;
        }

        if (file_exists(JPATH_SITE . '/media/lib_yireo/css/' . $stylesheet)) {
            $url = Uri::root() . 'media/lib_yireo/css/' . $stylesheet;
            $wa->registerAndUseStyle('yireo-' . md5($url), $url);

            return;
        }
    }

    /**
     * Add a specific JavaScript-script to this page.
     *
     * @param string $script
     */
    public function addJs($script)
    {
        $wa       = $this->doc->getWebAssetManager();
        $prefix   = ($this->app->isClient('site')) ? 'site-' : 'backend-';
        $template = $this->app->getTemplate();

        if (file_exists(JPATH_SITE . '/templates/' . $template . '/js/' . $this->getConfig('option') . '/' . $prefix . $script)) {
            $url = Uri::root() . 'templates/' . $template . '/js/' . $this->getConfig('option') . '/' . $prefix . $script;
            $wa->registerAndUseScript('yireo-' . md5($url), $url);

            return;
        }

        if (file_exists(JPATH_SITE . '/media/' . $this->getConfig('option') . '/js/' . $prefix . $script)) {
            $url = Uri::root() . 'media/' . $this->getConfig('option') . '/js/' . $prefix . $script;
            $wa->registerAndUseScript('yireo-' . md5($url), $url);

            return;
        }

        if (file_exists(JPATH_SITE . '/templates/' . $template . '/js/' . $this->getConfig('option') . '/' . $script)) {
            $url = Uri::root() . 'templates/' . $template . '/js/' . $this->getConfig('option') . '/' . $script;
            $wa->registerAndUseScript('yireo-' . md5($url), $url);

            return;
        }

        if (file_exists(JPATH_SITE . '/media/' . $this->getConfig('option') . '/js/' . $script)) {
            $url = Uri::root() . 'media/' . $this->getConfig('option') . '/js/' . $script;
            $wa->registerAndUseScript('yireo-' . md5($url), $url);

            return;
        }

        if (file_exists(JPATH_SITE . '/media/lib_yireo/js/' . $script)) {
            $url = Uri::root() . 'media/lib_yireo/js/' . $script;
            $wa->registerAndUseScript('yireo-' . md5($url), $url);

            return;
        }
    }

    /**
     * Add a folder to the template-search path.
     *
     * @param string $path
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
     * An override of the original JView-function to allow template files across multiple layouts.
     *
     * @param string $file
     * @param array $variables
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
            $this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $option . '/tmpl/' . $view, true);
            $this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $option . '/views/' . $view . '/tmpl', true);

            // Library defaults
            $this->addNewTemplatePath(JPATH_LIBRARIES . '/yireo/view/' . $view, false);
            $this->addNewTemplatePath(JPATH_LIBRARIES . '/yireo/view/' . $this->_viewParent, false);

            $this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $option . '/lib/view/' . $this->_viewParent, false);
            $this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $option . '/libraries/view/' . $this->_viewParent, false);
        } else {
            $template = $this->app->getTemplate();

            // Local layout
            $this->addNewTemplatePath(JPATH_SITE . '/components/' . $option . '/tmpl/' . $view, true);
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
            $layout = $this->getLayout();
            $file = ($layout !== null && $layout !== '') ? $layout . '.php' : 'default.php';
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
            throw new \RuntimeException('Template file can not be located: ' . $this->_viewParent . '/' . $file);
        }

        return '';
    }
}
