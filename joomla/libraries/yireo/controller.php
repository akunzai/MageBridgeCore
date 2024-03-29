<?php

/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   0.6.0
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import the loader
require_once dirname(__FILE__) . '/loader.php';

/**
 * Yireo Controller
 *
 * @package Yireo
 */
class YireoController extends YireoCommonController
{
    /**
     * Value of the minimum supported PHP version
     *
     * @constant
     */
    public const PHP_SUPPORTED_VERSION = '7.4.0';

    /**
     * Unique identifier
     *
     * @var int
     */
    protected $id = 0;

    /**
     * Value of the default View to use
     *
     * @protected string
     */
    protected $default_view = 'home';

    /**
     * Value of the current model
     *
     * @protected object
     */
    protected $model;

    /**
     * Boolean to allow or disallow frontend editing
     *
     * @protected bool
     */
    protected $frontend_edit = false;

    /**
     * List of allowed tasks
     *
     * @protected array
     */
    protected $allow_tasks = [
        'display',
    ];

    /**
     * List of POST-values that should be allowed to contain raw content
     *
     * @protected array
     */
    protected $allow_raw = [
        'description',
        'text',
        'comment',
    ];

    /**
     * List of relations between Views
     *
     * @protected int
     */
    protected $relations = [
        'list'     => 'lists',
        'category' => 'categories',
        'item'     => 'items',
        'status'   => 'statuses',
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        // Register extra tasks
        $this->registerTask('new', 'add');
        $this->registerTask('change', 'edit');

        // Allow or disallow frontend editing
        if ($this->app->isClient('site') && in_array($this->input->getCmd('task', 'display'), $this->allow_tasks) == false) {
            throw new \Yireo\Exception\Controller\IllegalRequest(Text::_('LIB_YIREO_CONTROLLER_ILLEGAL_REQUEST') . ' = ' . $this->input->getCmd('task'));
        }

        // Check for ACLs in backend
        if ($this->app->isClient('administrator')) {
            $user = version_compare(JVERSION, '4.0.0', '<')
                ? Factory::getUser()
                : Factory::getApplication()->getIdentity();

            if ($user->authorise('core.manage', $this->input->getCmd('option')) == false) {
                $this->app->redirect('index.php', Text::_('LIB_YIREO_CONTROLLER_ILLEGAL_REQUEST'));
            }
        }
    }

    /**
     * Display the current page
     *
     * @param bool $cachable
     * @param bool $urlparams
     *
     * @return \Joomla\CMS\MVC\Controller\BaseController
     */
    public function display($cachable = false, $urlparams = false)
    {
        // Set the layout properly
        if (in_array($this->input->get('format'), ['pdf', 'print'])) {
            $this->input->set('layout', 'print');
        }

        if ($this->input->get('view') == 'home') {
            $this->showPhpSupported();
        }

        return parent::display($cachable, $urlparams);
    }

    /**
     * Handle the task 'add'
     */
    public function add()
    {
        $this->input->set('edit', false);
        $this->setEditForm();
    }

    /**
     * Handle the task 'edit'
     */
    public function edit()
    {
        $this->input->set('edit', true);

        $model = $this->_loadModel();
        $model->checkout();

        $this->setEditForm();
    }

    /**
     * Handle the task 'copy'
     */
    public function copy()
    {
        $this->input->set('edit', false);
        $this->setEditForm();
    }

    /**
     * Load the POST data
     *
     * @return array
     */
    public function loadPost()
    {
        $inputPost = $this->input->post;
        return $inputPost->getArray();
    }

    /**
     * Handle the task 'store'
     *
     * @param array $post
     *
     * @return int
     */
    public function store($post = null)
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Fetch the POST-data
        if (empty($post)) {
            $post = $this->loadPost();
        }

        // Fetch the ID
        $post['id'] = $this->getId();
        $this->id   = $post['id'];

        // Make sure fields that are configured as "raw" are loaded correspondingly
        if (!empty($this->allow_raw)) {
            foreach ($this->allow_raw as $raw) {
                if (isset($post[$raw])) {
                    $post[$raw] = $this->input->get($raw, '', 'raw');
                }

                if (isset($post['item'][$raw])) {
                    $array = $this->input->getArray(['item' => [$raw => 'raw']]);
                    $post['item'][$raw] = $array['item'][$raw];
                }
            }
        }

        // Check for an alias
        if (in_array('alias', $post)) {
            if (empty($post['alias'])) {
                $alias         = $this->input->post->getString('title');
                $alias         = strtolower(OutputFilter::stringURLSafe($alias));
                $post['alias'] = $alias;
            }
        }

        // Get the model
        $model = $this->_loadModel();

        // Store these data with the model
        try {
            $model->store($post);
            $id = $model->getId();

            if ($id > 0) {
                $this->id = $id;
            }

            $this->msg = Text::sprintf('LIB_YIREO_CONTROLLER_ITEM_SAVED', $this->input->getCmd('view'));
        } catch (Exception $e) {
            $this->msg = Text::sprintf('LIB_YIREO_CONTROLLER_ITEM_NOT_SAVED', $this->input->getCmd('view'));
            $error     = $e->getMessage();

            if (!empty($error)) {
                $this->msg .= ': ' . $error;
            }

            $this->msg_type = 'error';
        }

        // Checkin the model, so it can be edited
        $model->checkin();

        return $this->id;
    }

    /**
     * Handle the task 'save'
     */
    public function save()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Store the data
        $this->store();

        // Determine the state of the model
        $model = $this->_loadModel();

        if (!method_exists($model, 'hasErrors') || $model->hasErrors() == false) {
            // Redirect back to the overview
            $plural = $this->getPluralName($this->input->get('view'));
            $this->doRedirect($plural);
        } else {
            // Redirect back to the form-page
            $this->doRedirect($this->input->get('view'), ['id' => $this->getId(), 'task' => 'edit']);
        }
    }

    /**
     * Handle the task 'apply'
     */
    public function apply()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Store the data
        $this->store();

        // Redirect back to the form-page
        $apply_url = $this->input->getString('apply_url');

        if (!empty($apply_url)) {
            return $this->setRedirect($apply_url, $this->msg, $this->msg_type);
        }

        $this->doRedirect($this->input->get('view'), ['id' => $this->getId(), 'task' => 'edit']);
    }

    /**
     * Handle the task 'savenew'
     */
    public function savenew()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Store the data
        $this->store();

        // Redirect to the form-page
        $this->doRedirect($this->input->get('view'), ['id' => 0, 'task' => 'add']);
    }

    /**
     * Handle the task 'saveandcopy'
     */
    public function saveandcopy()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Store these data
        $this->store();

        // Remove the identifier from whereever
        $this->input->set('id', 0);
        $this->input->set('cid[]', 0);
        $this->input->set('cid', null);
        $this->setId(0);

        // Store these data
        $id = $this->store();

        // Redirect to the form-page
        $this->doRedirect($this->input->get('view'), ['id' => $id, 'task' => 'copy']);
    }

    /**
     * Handle the task 'saveascopy'
     */
    public function saveascopy()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Remove the identifier from whereever
        $this->input->set('id', 0);
        $this->input->set('cid[]', 0);
        $this->input->set('cid', null);

        // Store these data
        $this->store();

        // Redirect to the form-page
        $this->doRedirect($this->input->get('view'), ['id' => $this->getId(), 'task' => 'copy']);
    }

    /**
     * Handle the task 'remove'
     */
    public function remove()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Get the ID-list
        $cid = $this->getIds();

        if (count($cid) < 1) {
            throw new Exception(Text::_('LIB_YIREO_CONTROLLER_ITEM_SELECT_DELETE'));
        }

        // Remove all selected items
        $model = $this->_loadModel();
        $model->delete($cid);

        if (count($cid) == 1) {
            $singleName = $this->getSingleName($this->input->getCmd('view'));
            $this->msg  = Text::_('LIB_YIREO_CONTROLLER_' . strtoupper($singleName) . '_DELETED');
        } else {
            $pluralName = $this->getPluralName($this->input->getCmd('view'));
            $this->msg  = Text::sprintf('LIB_YIREO_CONTROLLER_' . strtoupper($pluralName) . '_DELETED', count($cid));
        }

        // Redirect to this same page
        $this->doRedirect();
    }

    /**
     * Handle the task 'publish'
     */
    public function publish()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Get the ID-list
        $cid = $this->getIds();

        if (count($cid) < 1) {
            throw new Exception(Text::_('LIB_YIREO_CONTROLLER_ITEM_SELECT_PUBLISH'));
        }

        // Use the model to publish this entry
        $model = $this->_loadModel();

        try {
            $model->publish($cid, 1);
            $this->msg = (count($cid) == 1) ? Text::_('LIB_YIREO_CONTROLLER_ITEM_PUBLISHED') : Text::sprintf('LIB_YIREO_CONTROLLER_ITEM_PUBLISHED', count($cid));
        } catch (Exception $e) {
            echo "<script> alert('" . $e->getMessage() . "'); window.history.go(-1); </script>\n";
        }

        // Redirect to this same page
        $this->doRedirect();
    }

    /**
     * Handle the task 'unpublish'
     */
    public function unpublish()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Get the ID-list
        $cid = $this->getIds();

        if (count($cid) < 1) {
            throw new Exception(Text::_('LIB_YIREO_CONTROLLER_ITEM_SELECT_UNPUBLISH'));
        }

        // Use the model to unpublish this entry
        $model = $this->_loadModel();

        try {
            $model->publish($cid, 0);
            if (count($cid) == 1) {
                $singleName = $this->getSingleName($this->input->getCmd('view'));
                $this->msg  = Text::sprintf('LIB_YIREO_CONTROLLER_ITEM_UNPUBLISHED', $singleName);
            } else {
                $pluralName = $this->getPluralName($this->input->getCmd('view'));
                $this->msg  = Text::sprintf('LIB_YIREO_CONTROLLER_ITEM_UNPUBLISHED', $pluralName, count($cid));
            }
        } catch (Exception $e) {
            echo "<script> alert('" . $e->getMessage() . "'); window.history.go(-1); </script>\n";
        }

        // Redirect to this same page
        $this->doRedirect();
    }

    /**
     * Handle the task 'cancel'
     */
    public function cancel()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Checkin the model
        $model = $this->_loadModel();
        $model->checkin();
        $model->resetTmpSession();

        // Redirect back to the overview page
        $plural = $this->getPluralName($this->input->get('view'));
        $this->doRedirect($plural);
    }

    /**
     * Handle the task 'orderup'
     */
    public function orderup()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Order-up using the model
        $model = $this->_loadModel();
        $model->move(-1);

        // Redirect to this same page
        $this->doRedirect();
    }

    /**
     * Handle the task 'orderdown'
     */
    public function orderdown()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Order-down using the model
        $model = $this->_loadModel();
        $model->move(1);

        // Redirect to this same page
        $this->doRedirect();
    }

    /**
     * Handle the task 'saveorder'
     */
    public function saveorder()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Fetch the current ID-list
        $cid = $this->getIds();

        // Fetch the ordering-list
        $order = $this->input->get('order', [], 'post', 'array');
        ArrayHelper::toInteger($order);

        // Auto-correct ordering with only zeros
        if (!empty($order)) {
            $only_zero = true;

            foreach ($order as $o) {
                if ($o > 0) {
                    $only_zero = false;
                }
            }

            if ($only_zero == true) {
                $j = 1;

                foreach ($order as $i => $o) {
                    $order[$i] = $j;
                    $j++;
                }
            }
        }

        // Save these data in the model
        $model = $this->_loadModel();
        $model->saveorder($cid, $order);

        // Redirect to this same page
        $this->doRedirect();
    }

    /**
     * Handle the task 'vote'
     */
    public function vote()
    {
        // Security check
        Session::checkToken() or exit(Text::_('JINVALID_TOKEN'));

        // Fetch base-variables
        $url    = $this->input->get('url', '', 'default', 'string');
        $rating = $this->input->get('user_rating', 0, '', 'int');
        $id     = $this->input->get('cid', 0, '', 'int');

        // Load the current model
        /** @var MageBridgeModelItem */
        $model = $this->getModel('item');
        $model->setId($id);

        // If this vote is made from an external source, make sure we redirect to an internal page
        if (!Uri::isInternal($url)) {
            $option = $this->input->getCmd('option');
            $view   = $this->input->getCmd('view');
            $url    = Route::_('index.php?option=' . $option . '&view=' . $view . '&id=' . $id);
        }

        // Store the vote in this model
        if ($model->storeVote($rating)) {
            $this->setRedirect($url, Text::_('LIB_YIREO_CONTROLLER_ITEM_VOTE_SUCCESS'));
        } else {
            $this->setRedirect($url, Text::_('LIB_YIREO_CONTROLLER_ITEM_VOTE_ALREADY'));
        }
    }

    /**
     * Handle the task 'toggle'
     */
    public function toggle()
    {
        // Security check
        Session::checkToken('get') or exit(Text::_('JINVALID_TOKEN'));

        // Fetch the request-parameters
        $id    = $this->input->getInt('id');
        $name  = $this->input->getCmd('name');
        $value = $this->input->getInt('value');

        if ($id > 0 && strlen($name) > 0) {
            $model = $this->_loadModel();

            if (method_exists($model, 'toggle')) {
                $model->toggle($id, $name, $value);
            }
        }

        // Redirect to this same page
        $this->doRedirect();
    }

    /** Helper function to set the form page
     *
     * @return bool
     */
    protected function setEditForm()
    {
        // If we are in a "plural" view, redirect to a "single" view
        $current = $this->input->getCmd('view');
        $single  = $this->getSingleName($current);

        // If the current request does not have the right view, redirect to the right view
        if ($current != $single) {
            $id        = $this->getId();
            $variables = ['task' => $this->input->getCmd('task')];

            if ($id > 0) {
                $variables['id'] = $id;
            }

            $this->doRedirect($single, $variables);

            return false;
        }

        // Hide the menu while editing or adding an item
        $this->input->set('hidemainmenu', 1);

        // Display this page
        parent::display();

        return true;
    }

    /**
     * Helper function to load the current model
     *
     * @return YireoModel
     */
    protected function _loadModel()
    {
        if ($this->model !== null) {
            return $this->model;
        }

        // Derive the model-name from the current view
        $name = $this->getSingleName($this->input->get('view'));

        // Create the model-object from the singular model-name
        $model = $this->getModel($name);

        // If it is still empty, try to create the model manually instead
        if (empty($model)) {
            $model = new YireoModel($name, $name . 's', $name . '_id');
        }

        $this->model = $model;

        return $this->model;
    }

    /**
     * Helper function to het the plural form of a word
     *
     * @param string $name
     *
     * @return string
     */
    protected function getPluralName($name = '')
    {
        $relations = $this->relations;

        if (isset($relations[$name])) {
            return $relations[$name];
        } else {
            if ($index = array_search($name, $relations)) {
                return $name;
            }
        }

        if (preg_match('/s$/', $name) == false) {
            return $name . 's';
        }

        return $name;
    }

    /**
     * Helper function to get the singular form of a word
     *
     * @param string $name
     *
     * @return string
     */
    protected function getSingleName($name = '')
    {
        $relations = $this->relations;

        if (array_key_exists($name, $relations)) {
            return $name;
        } else {
            if ($index = array_search($name, $relations)) {
                return $index;
            } else {
                if (preg_match('/ses$/', $name)) {
                    return preg_replace('/es$/', '', $name);
                }
            }
        }

        return preg_replace('/s$/', '', $name);
    }

    /**
     * Method to set the proper redirect
     *
     * @param string $view
     * @param array  $variables
     *
     * @return bool
     */
    protected function doRedirect($view = '', $variables = [])
    {
        // Detect the current view if it is not explicitely set
        if (empty($view)) {
            $view = $this->input->getCmd('view');
        }

        // Fetch the current component name
        $option = $this->input->getCmd('option');

        // Construct the URL
        $link = 'index.php?option=' . $option . '&view=' . $view;

        // Add a modal flag
        if ($this->input->getInt('modal') == 1) {
            $variables['modal'] = 1;
            $variables['tmpl']  = 'component';
        }

        // Add the extra variables to the URL if needed
        if (!empty($variables)) {
            foreach ($variables as $name => $value) {
                $link .= '&' . $name . '=' . $value;
            }
        }

        // Set the redirect, including messages if they are set
        if ($this->app->isClient('site')) {
            $link = Route::_($link);
        }

        $this->setRedirect($link, $this->msg, $this->msg_type);

        return true;
    }

    /**
     * Manually set the ID
     *
     * @param int
     */
    protected function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Method to get the current ID
     *
     * @return int
     */
    protected function getId()
    {
        // Return the internal ID if it is set
        if (isset($this->id) && $this->id > 0) {
            return $this->id;
        }

        $cid = $this->input->get('cid', [0], null, 'array');
        $id  = (int) $cid[0];

        if (!empty($id)) {
            $this->id = $id;

            return $this->id;
        }

        $id = $this->input->getInt('id');

        if (!empty($id)) {
            $this->id = $id;

            return $this->id;
        }

        return $this->id;
    }

    /**
     * Method to get the selected IDs
     *
     * @return array
     */
    protected function getIds()
    {
        // Fetch the single ID
        $id = $this->input->getInt('id');

        if ($id > 0) {
            return [$id];
        }

        // Fetch the ID-list and make sure it renders as a list of numbers
        $cid = $this->input->get('cid', [0], 'post', 'array');
        ArrayHelper::toInteger($cid);

        return $cid;
    }

    /**
     * Method to check whether the current PHP version is supported
     */
    protected function showPhpSupported()
    {
        $phpversion = phpversion();
        if (version_compare($phpversion, self::PHP_SUPPORTED_VERSION, 'lt')) {
            $message = Text::sprintf('LIB_YIREO_PHP_UNSUPPORTED', $phpversion, self::PHP_SUPPORTED_VERSION);
            $this->app->enqueueMessage($message, 'error');
        }
    }
}
