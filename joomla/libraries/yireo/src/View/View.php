<?php

declare(strict_types=1);

namespace Yireo\View;

defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Yireo\Exception\View\ModelNotFound;
use Yireo\Helper\Helper;
use Yireo\Helper\PathHelper;

/**
 * Yireo View.
 */
class View extends CommonView
{
    /**
     * Array of HTML-lists for usage in the layout-file.
     *
     * @var array
     */
    protected $lists = [];

    /**
     * Array of HTML-grid-elements for usage in the layout-file.
     *
     * @var array
     */
    protected $grid = [];

    /**
     * Flag to determine whether to autoclean item-properties or not.
     *
     * @var bool
     */
    protected $autoclean = false;

    /**
     * Flag to determine whether to load the menu.
     *
     * @var bool
     */
    protected $loadToolbar = true;

    /**
     * Flag to prepare the display-data.
     *
     * @var bool
     */
    protected $prepare_display = true;

    /**
     * The pagination object.
     *
     * @var \Joomla\CMS\Pagination\Pagination|null
     */
    protected $pagination;

    /**
     * @var \Joomla\Registry\Registry|null
     */
    protected $params;

    /**
     * @var object|null
     */
    protected $item;

    /**
     * @var array|null
     */
    protected $items;

    /**
     * @var \Yireo\Model\ModelItem|\Yireo\Model\ModelItems|\Joomla\CMS\MVC\Model\BaseDatabaseModel|null
     */
    protected $model;

    /**
     * @var \Yireo\Table\Table|\Joomla\CMS\Table\Table|null
     */
    protected $table;

    /**
     * @var int
     */
    protected $total;

    /**
     * @var \Joomla\CMS\Form\Form|null
     */
    protected $form;

    /**
     * Main constructor method.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        // Call the parent constructor
        parent::__construct($config);

        // Set the parameters
        if ($this->params === null) {
            if ($this->app->isClient('site') == false) {
                $this->params = ComponentHelper::getParams($this->getConfig('option'));
            } else {
                /** @var \Joomla\CMS\Application\SiteApplication $siteApp */
                $siteApp = $this->app;
                $this->params = $siteApp->getParams($this->getConfig('option'));
            }
        }

        // Determine whether this view is single or not
        if ($this->_single === null) {
            $viewName = $this->getConfig('view');

            if (preg_match('/s$/', $viewName)) {
                $this->_single = false;
            } else {
                $this->_single = true;
            }
        }

        // Insert the model & table
        $this->model  = $this->getModel(null, false);

        if ($this->model !== null && $this->model !== false && method_exists($this->model, 'getTable')) {
            $useTable = false;

            if ($this->model instanceof \Yireo\Model\CommonModel === false) {
                $useTable = true;
            } else {
                if ($this->model->getConfig('skip_table') === false) {
                    $useTable = true;
                }
            }

            if ($useTable === true) {
                $this->table  = $this->model->getTable();
            }
        }

        // Add some backend-elements
        if ($this->app->isClient('administrator')) {
            // Automatically set the title
            $this->setTitle();
            $this->setMenu();
            $this->setAutoclean(true);

            // Add some things to the task-bar
            if ($this->_single && $this->loadToolbar == true && $this->params !== null) {
                if ($this->params->get('toolbar_show_savenew', 1)) {
                    ToolbarHelper::custom('savenew', 'save', '', 'LIB_YIREO_VIEW_TOOLBAR_SAVENEW', false);
                }

                if ($this->params->get('toolbar_show_saveandcopy', 1)) {
                    ToolbarHelper::custom('saveandcopy', 'copy', '', 'LIB_YIREO_VIEW_TOOLBAR_SAVEANDCOPY', false);
                }

                if ($this->params->get('toolbar_show_saveascopy', 1)) {
                    ToolbarHelper::custom('saveascopy', 'copy', '', 'LIB_YIREO_VIEW_TOOLBAR_SAVEASCOPY', false);
                }

                ToolbarHelper::save();
                ToolbarHelper::apply();
                ToolbarHelper::cancel('cancel', $this->isEdit() == false ? 'JTOOLBAR_CANCEL' : 'LIB_YIREO_VIEW_TOOLBAR_CLOSE');
            }
        }
    }

    /**
     * Main display method.
     *
     * @param string $tpl
     */
    public function display($tpl = null)
    {
        if ($this->prepare_display == true) {
            $this->prepareDisplay();
        }

        if (empty($tpl)) {
            $tpl = $this->getLayout();
        }

        parent::display($tpl);
    }

    /**
     * Method to prepare for displaying.
     */
    public function prepareDisplay()
    {
        // Include extra component-related CSS
        $this->addCss('default.css');
        $this->addCss('view-' . $this->getConfig('view') . '.css');
        $this->addCss('j35.css');

        // Include extra component-related JavaScript
        $this->addJs('default.js');
        $this->addJs('view-' . $this->getConfig('view') . '.js');

        // Fetch parameters if they exist
        $params = null;

        if (!empty($this->item->params)) {
            $option = $this->getConfig('option');
            $componentPath = PathHelper::getAdministratorPath() . '/components/' . $option;
            if (file_exists($componentPath . '/models/' . $this->_name . '.xml')) {
                $file   = $componentPath . '/models/' . $this->_name . '.xml';
                $params = Helper::toRegistry($this->item->params, $file);
            } else {
                if (!empty($this->item->params)) {
                    $params = Helper::toRegistry($this->item->params);
                }
            }
        }

        // Assign parameters
        if (!empty($params)) {
            if (isset($this->item->created)) {
                $params->set('created', $this->item->created);
            }

            if (isset($this->item->created_by)) {
                $params->set('created_by', $this->item->created_by);
            }

            if (isset($this->item->modified)) {
                $params->set('modified', $this->item->modified);
            }

            if (isset($this->item->modified_by)) {
                $params->set('modified_by', $this->item->modified_by);
            }

            $this->params = $params;
        }

        // Load the form if it's there
        /** @phpstan-ignore-next-line */
        $form = $this->get('Form');

        if (!empty($form)) {
            $this->form = $form;
        }
    }

    /**
     * Helper-method to set a specific filter.
     *
     * @param string $filter
     * @param string $default
     * @param string $type
     * @param string $option
     *
     * @return mixed
     */
    protected function getFilter($filter = '', $default = '', $type = 'cmd', $option = '')
    {
        if (empty($option)) {
            $option = $this->getConfig('option_id');
        }

        $value = $this->app->getUserStateFromRequest($option . 'filter_' . $filter, 'filter_' . $filter, $default, $type);

        return $value;
    }

    /**
     * Helper-method to get multiple items from the MVC-model.
     *
     * @return array
     */
    protected function fetchItems()
    {
        // Get data from the model
        if (empty($this->items)) {
            /** @phpstan-ignore-next-line */
            $this->total      = $this->get('Total');
            /** @phpstan-ignore-next-line */
            $this->pagination = $this->get('Pagination');
            /** @phpstan-ignore-next-line */
            $this->items      = $this->get('Data');
        }

        if (!empty($this->items)) {
            foreach ($this->items as $index => $item) {
                // Clean data
                if ($this->autoclean == true) {
                    OutputFilter::objectHTMLSafe($item, ENT_QUOTES, 'text');

                    if (isset($item->text)) {
                        $item->text = htmlspecialchars($item->text);
                    }

                    if (isset($item->description)) {
                        $item->description = htmlspecialchars($item->description);
                    }
                }

                // Reinsert this item
                $this->items[$index] = $item;
            }
        }

        // Get other data from the model
        $this->lists['search_name'] = 'filter_search';
        $this->lists['search']      = $this->getFilter('search', '', 'string');
        $this->lists['order']       = $this->getFilter('order', '', 'string');
        $this->lists['order_Dir']   = $this->getFilter('order_Dir');
        $this->lists['state']       = HTMLHelper::_('grid.state', $this->getFilter('state'));

        return $this->items;
    }

    /**
     * Helper-method to get a single item from the MVC-model.
     *
     * @throws ModelNotFound
     *
     * @return object|null
     */
    protected function fetchItem()
    {
        if ($this->item !== null) {
            return $this->item;
        }

        // Fetch the model
        $this->model = $this->getModel(null, false);

        if ($this->model === null) {
            throw new ModelNotFound('Unable to find YireoModel');
        }

        // Determine if this is a new item or not
        $primary_key = (method_exists($this->model, 'getPrimaryKey')) ? $this->model->getPrimaryKey() : 'id';
        $this->item  = (method_exists($this->model, 'getData')) ? $this->model->getData() : (object) null;
        $isNew       = (method_exists($this->model, 'getId') && $this->model->getId() > 0) ? true : false;

        // Override in case of copying
        if ($this->input->getCmd('task') === 'copy') {
            $this->item->$primary_key = 0;
            $isNew                    = true;
        }

        // If there is a key, fetch the data
        if ($isNew === false) {
            // Extra checks in the backend
            if ($this->app->isClient('administrator') && $this->item !== null) {
                // Fail if checked-out not by current user
                if (method_exists($this->model, 'isCheckedOut') && $this->model->isCheckedOut($this->item)) {
                    $msg = Text::_('LIB_YIREO_MODEL_CHECKED_OUT');
                    $this->app->enqueueMessage($msg, 'error');
                    $this->app->redirect('index.php?option=' . $this->getConfig('option'));
                }

                // Checkout older items
                if (method_exists($this->model, 'checkout')) {
                    $this->model->checkout($this->user->id);
                }
            }

            // Clean data
            if ($this->app->isClient('administrator') === false || ($this->input->getCmd('task') !== 'edit' && $this->_viewParent !== 'form')) {
                $this->autocleanItem();
            }
        }

        // Automatically hit this item
        if ($this->app->isClient('site') && method_exists($this->model, 'hit')) {
            $this->model->hit();
        }

        $this->assignList();

        return $this->item;
    }

    /**
     * @return bool
     */
    private function autocleanItem()
    {
        if ($this->autoclean === false) {
            return false;
        }

        OutputFilter::objectHTMLSafe($this->item, ENT_QUOTES, 'text');

        if (isset($this->item->title)) {
            $this->item->title = htmlspecialchars($this->item->title);
        }

        if (isset($this->item->text)) {
            $this->item->text = htmlspecialchars($this->item->text);
        }

        if (isset($this->item->description)) {
            $this->item->description = htmlspecialchars($this->item->description);
        }

        return true;
    }

    private function assignList()
    {
        $this->assignListPublished();
        $this->assignListAccess();
        $this->assignListOrdering();
    }

    private function assignListPublished()
    {
        if ($this->item !== null && isset($this->item->published)) {
            if (class_exists('\YireoFormFieldPublished')) {
                $this->lists['published'] = \YireoFormFieldPublished::getFieldInput($this->item->published);
            } else {
                $this->lists['published'] = null;
            }
        } else {
            $this->lists['published'] = null;
        }
    }

    private function assignListAccess()
    {
        // list.accesslevel was removed in Joomla 5, skip this assignment
        $this->lists['access'] = null;
    }

    private function assignListOrdering()
    {
        // list.ordering was removed in Joomla 5, skip this assignment
        $this->lists['ordering'] = null;
    }

    /**
     * Add the AJAX-script to the page.
     *
     * @param string|null $url
     * @param string|null $div
     *
     * @return mixed
     */
    public function ajax($url = null, $div = null)
    {
        if (class_exists('\YireoHelperView')) {
            return \YireoHelperView::ajax($url, $div);
        }
        return null;
    }

    /**
     * Add the AJAX-script to the page.
     */
    public function getAjaxFunction()
    {
        $script = <<<'EOT'
            <script type="text/javascript">
                function getAjax(ajax_url, element_id, type) {
                    var MBajax = jQuery.ajax({
                        url: ajax_url,
                        method: 'get', 
                        success: function(result){
                            if (result == '') {
                                alert('Empty result');
                            } else {
                                jQuery('#' + element_id).val(result);
                            }
                        }
                    });
                }
            </script>
            EOT;
        $this->doc->addCustomTag($script);
    }

    /**
     * Automatically decode HTML-characters from specified item-fields.
     *
     * @param bool $autoclean
     */
    public function setAutoClean($autoclean = true)
    {
        $this->autoclean = $autoclean;
    }

    /**
     * Overload the original method.
     *
     * @param string|null $name
     * @param bool $generateFatalError
     *
     * @throws ModelNotFound
     *
     * @return \Joomla\CMS\MVC\Model\BaseDatabaseModel|\Yireo\Model\ModelItem|\Yireo\Model\ModelItems|null
     */
    public function getModel($name = null, $generateFatalError = true)
    {
        if (empty($name)) {
            $name = $this->_name;
        }

        $name = strtolower($name);

        $model = null;
        if (isset($this->_models[$name])) {
            $model = $this->_models[$name];
        }

        if (empty($model)) {
            /** @phpstan-ignore-next-line */
            BaseDatabaseModel::addIncludePath(PathHelper::getAdministratorPath() . '/components/' . $this->getConfig('option') . '/models');

            $classPrefix = ucfirst(preg_replace('/^com_/', '', $this->getConfig('option'))) . 'Model';
            $classPrefix = preg_replace('/[^A-Z0-9_]/i', '', $classPrefix);
            $classPrefix = str_replace(' ', '', ucwords(str_replace('_', ' ', $classPrefix)));

            /** @phpstan-ignore-next-line */
            $model = BaseDatabaseModel::getInstance($name, $classPrefix, []);
        }

        if (empty($model) && $generateFatalError == true) {
            throw new ModelNotFound('YireoModel not found');
        }

        return $model;
    }

    /**
     * Helper method to display a certain grid-header.
     *
     * @param string $type
     * @param string $title
     *
     * @return string|null
     */
    public function getGridHeader($type, $title)
    {
        $html = null;

        if ($type == 'orderby' && $this->model !== null) {
            /** @phpstan-ignore-next-line */
            $field = $this->get('OrderByDefault');
            $html  .= HTMLHelper::_('grid.sort', $title, $field, $this->lists['order_Dir'], $this->lists['order']);
            $html  .= HTMLHelper::_('grid.order', $this->items);
        }

        return $html;
    }

    /**
     * Helper method to display a certain grid-cell.
     *
     * @param string $type
     * @param object $item
     * @param int $i
     * @param int $n
     *
     * @return string|null
     */
    public function getGridCell($type, $item, $i = 0, $n = 0)
    {
        $html = null;

        if ($type == 'reorder' && $this->model !== null && $this->pagination !== null) {
            /** @phpstan-ignore-next-line */
            $field    = $this->get('OrderByDefault');
            $ordering = ($this->lists['order'] == $field);
            $disabled = ($ordering) ? '' : 'disabled="disabled"';

            $html .= '<span>' . $this->pagination->orderUpIcon($i, true, 'orderup', 'Move Up', $ordering) . '</span>';
            $html .= '<span>' . $this->pagination->orderDownIcon($i, $n, true, 'orderdown', 'Move Down', $ordering) . '</span>';
            $html .= '<input type="text" name="order[]" size="5" value="' . $item->$field . '" ' . $disabled . ' class="text_area" style="text-align: center" />';

            return $html;
        }

        if ($type == 'published' && isset($item->params)) {
            $html .= HTMLHelper::_('jgrid.published', $item->published, $i, 'articles.', false, 'cb', $item->params->get('publish_up'), $item->params->get('publish_down'));

            return $html;
        }

        if ($type == 'checked') {
            $html .= HTMLHelper::_('grid.checkedout', $item, $i);
        }

        return $html;
    }

    /**
     * Method to return img-tag for a certain image, if that image exists.
     *
     * @param string $name
     *
     * @return string
     */
    public function getImageTag($name = null)
    {
        $paths = [
            '/media/' . $this->getConfig('option') . '/images/' . $name,
            '/media/lib_yireo/images/' . $name,
            '/images/' . $name,
        ];

        foreach ($paths as $path) {
            if (file_exists(PathHelper::getSitePath() . $path)) {
                return '<img src="' . $path . '" alt="' . $name . '" />';
            }
        }
        return '';
    }

    /**
     * Override original method.
     *
     * @throws \Exception
     *
     * @return string|bool The name of the model
     */
    public function getName()
    {
        $name = $this->_name;

        if (empty($name)) {
            $match = null;

            if (!preg_match('/View((view)*(.*(view)?.*))$/i', get_class($this), $match)) {
                throw new \Exception("JView::getName() : Cannot get or parse class name.", 500);
            }

            $name = strtolower($match[3]);
        }

        return $name;
    }

    /**
     * Add a layout to this view.
     *
     * @param string $name
     * @param array $variables
     */
    public function loadLayout($name = null, $variables = [])
    {
        $name = $this->getLayoutPrefix() . $name;

        // Merge current object variables
        $variables = array_merge($variables, get_object_vars($this));

        $basePath = null;
        $layout   = new FileLayout($name, $basePath);

        echo $layout->render($variables);
    }

    /**
     * Return a common prefix for all layouts in this component.
     *
     * @return string
     */
    public function getLayoutPrefix()
    {
        return '';
    }
}
