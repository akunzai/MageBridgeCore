<?php

/**
 * Joomla! Yireo Library.
 *
 * @author    Yireo (https://www.yireo.com/)
 * @license   GNU Public License
 *
 * @link      https://www.yireo.com/
 */

// Namespace

namespace Yireo\System;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Class Autoloader.
 */
class Autoloader
{
    /**
     * Mapping of classes for Joomla 5+ compatibility.
     */
    protected $mapping = [
        'YireoRouteQuery'         => 'src/Route/Query',
        'Yireo\Route\Query' => 'src/Route/Query',
        'YireoDispatcher'         => 'src/System/Dispatcher',
        'Yireo\System\Dispatcher' => 'src/System/Dispatcher',
        'YireoModel'              => 'model', // Deprecated: Use YireoModelItem or YireoModelItems instead
        'YireoModelItem'          => 'src/Model/ModelItem',
        'YireoAbstractModel'      => 'src/Model/AbstractModel',
        'YireoCommonModel'        => 'src/Model/CommonModel',
        'YireoDataModel'          => 'src/Model/DataModel',
        'YireoServiceModel'       => 'src/Model/ServiceModel',
        'YireoModelItems'         => 'src/Model/ModelItems',
        'YireoView'               => 'src/View/View',
        'YireoCommonView'         => 'src/View/CommonView',
        'YireoAbstractView'       => 'src/View/AbstractView',
        'YireoViewHome'           => 'src/View/ViewHome',
        'YireoViewList'           => 'src/View/ViewList',
        'YireoViewItem'           => 'src/View/ViewItem',
        'YireoViewForm'           => 'src/View/ViewForm',
        'YireoController'         => 'src/Controller/Controller',
        'YireoCommonController'   => 'src/Controller/CommonController',
        'YireoAbstractController' => 'src/Controller/AbstractController',
        'YireoFormFieldPublished' => 'form/fields/published',
        'Yireo\Form\Field\AbstractField' => 'src/Form/Field/AbstractField',
        'Yireo\Form\Field\Article' => 'src/Form/Field/Article',
        'Yireo\Form\Field\Boolean' => 'src/Form/Field/Boolean',
        'Yireo\Form\Field\Components' => 'src/Form/Field/Components',
        'Yireo\Form\Field\File' => 'src/Form/Field/File',
        'Yireo\Form\Field\Published' => 'src/Form/Field/Published',
        'Yireo\Form\Field\Selecti' => 'src/Form/Field/Selecti',
        'Yireo\Form\Field\Text' => 'src/Form/Field/Text',
        'YireoTable'              => 'src/Table/Table',
        'Yireo\Table\Table' => 'src/Table/Table',
        'YireoHelper'             => 'src/Helper/Helper',
        'YireoHelperForm'         => 'src/Helper/Form',
        'YireoHelperInstall'      => 'src/Helper/Install',
        'YireoHelperTable'        => 'src/Helper/Table',
        'YireoHelperView'         => 'src/Helper/View',
        'Yireo\Helper\Helper' => 'src/Helper/Helper',
        'Yireo\Helper\Form' => 'src/Helper/Form',
        'Yireo\Helper\Install' => 'src/Helper/Install',
        'Yireo\Helper\Table' => 'src/Helper/Table',
        'Yireo\Helper\View' => 'src/Helper/View',
        // Namespaced backward compatibility mappings
        'Yireo\Model\AbstractModel' => 'src/Model/AbstractModel',
        'Yireo\Model\CommonModel' => 'src/Model/CommonModel',
        'Yireo\Model\Data\Query' => 'src/Model/Data/Query',
        'Yireo\Model\Data\Querytext' => 'src/Model/Data/Querytext',
        'Yireo\Model\DataModel' => 'src/Model/DataModel',
        'Yireo\Model\ModelItem' => 'src/Model/ModelItem',
        'Yireo\Model\ModelItems' => 'src/Model/ModelItems',
        'Yireo\Model\ServiceModel' => 'src/Model/ServiceModel',
        'Yireo\Model\Trait\Checkable' => 'src/Model/Trait/Checkable',
        'Yireo\Model\Trait\Configurable' => 'src/Model/Trait/Configurable',
        'Yireo\Model\Trait\Debuggable' => 'src/Model/Trait/Debuggable',
        'Yireo\Model\Trait\Filterable' => 'src/Model/Trait/Filterable',
        'Yireo\Model\Trait\Formable' => 'src/Model/Trait/Formable',
        'Yireo\Model\Trait\Identifiable' => 'src/Model/Trait/Identifiable',
        'Yireo\Model\Trait\Limitable' => 'src/Model/Trait/Limitable',
        'Yireo\Model\Trait\Paginable' => 'src/Model/Trait/Paginable',
        'Yireo\Model\Trait\Table' => 'src/Model/Trait/Table',
        'Yireo\Controller\AbstractController' => 'src/Controller/AbstractController',
        'Yireo\Controller\CommonController' => 'src/Controller/CommonController',
        'Yireo\Controller\Controller' => 'src/Controller/Controller',
        'Yireo\View\AbstractView' => 'src/View/AbstractView',
        'Yireo\View\CommonView' => 'src/View/CommonView',
        'Yireo\View\View' => 'src/View/View',
        'Yireo\View\ViewForm' => 'src/View/ViewForm',
        'Yireo\View\ViewHome' => 'src/View/ViewHome',
        'Yireo\View\ViewItem' => 'src/View/ViewItem',
        'Yireo\View\ViewList' => 'src/View/ViewList',
    ];

    /**
     * Main autoloading function.
     *
     * @return bool
     */
    public function load($className)
    {
        if (stristr($className, 'yireo') === false) {
            return false;
        }

        $rt = $this->loadLegacy($className);

        if ($rt === true) {
            return true;
        }

        // Try to include namespaced files
        $rt = $this->loadNamespaced($className);

        if ($rt === true) {
            return true;
        }

        return false;
    }

    /**
     * Autoloading function for namespaced classes.
     *
     * @return bool
     */
    protected function loadNamespaced($className)
    {
        $prefix   = 'Yireo\\';
        $baseDir = dirname(__DIR__) . '/';
        $len      = strlen($prefix);

        if (strncmp($prefix, $className, $len) !== 0) {
            return false;
        }

        $relativeClass = substr($className, $len);

        $filename = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (!file_exists($filename)) {
            return false;
        }

        include_once $filename;

        return true;
    }

    /**
     * Autoloading function for Joomla 5+ classes.
     *
     * @return bool
     */
    protected function loadLegacy($className)
    {
        // Preliminary check
        if (substr($className, 0, 5) != 'Yireo') {
            return false;
        }

        // Construct the filename
        if (isset($this->mapping[$className])) {
            $filename = $this->mapping[$className];
        } else {
            $className = preg_replace('/^Yireo/', '', $className);
            $pieces = preg_split('/(?=[A-Z])/', $className);
            $path = [];

            foreach ($pieces as $piece) {
                $path[] = strtolower($piece);
            }

            $filename = implode('/', $path);
        }

        // Try to determine the needed file
        $filename = dirname(dirname(__DIR__)) . '/' . $filename . '.php';

        if (!file_exists($filename)) {
            return false;
        }

        include_once $filename;

        return true;
    }
}
