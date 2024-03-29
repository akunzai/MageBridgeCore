<?php

/**
 * @package	 Joomla.Libraries
 * @subpackage  Module
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license	 GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Profiler\Profiler;
use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

define('MAGEBRIDGE_MODULEHELPER_OVERRIDE', true);

/**
 * Module helper class
 *
 * @package	 Joomla.Libraries
 * @subpackage  Module
 * @since	   1.5
 */
abstract class JModuleHelper
{
    /**
     * Get module by name (real, eg 'Breadcrumbs' or folder, eg 'mod_breadcrumbs')
     *
     * @param   string  $name   The name of the module
     * @param   string  $title  The title of the module, optional
     *
     * @return  object  The Module object
     *
     * @since   1.5
     */
    public static function &getModule($name, $title = null)
    {
        $result = null;
        $modules = &static::load();
        $total = count($modules);

        for ($i = 0; $i < $total; $i++) {
            // Match the name of the module
            if ($modules[$i]->name == $name || $modules[$i]->module == $name) {
                // Match the title if we're looking for a specific instance of the module
                if (!$title || $modules[$i]->title == $title) {
                    // Found it
                    $result = &$modules[$i];
                    break;
                }
            }
        }

        if (is_object($result) && isset($result->position) && MageBridgeTemplateHelper::allowPosition($result->position) == false) {
            $result = null;
        }

        // If we didn't find it, and the name is mod_something, create a dummy object
        if (is_null($result) && substr($name, 0, 4) == 'mod_') {
            $result            = new stdClass();
            $result->id        = 0;
            $result->title     = '';
            $result->module    = $name;
            $result->position  = '';
            $result->content   = '';
            $result->showtitle = 0;
            $result->control   = '';
            $result->params    = '';
        }

        return $result;
    }

    /**
     * Get modules by position
     *
     * @param   string  $position  The position of the module
     *
     * @return  array  An array of module objects
     *
     * @since   1.5
     */
    public static function &getModules($position)
    {
        $position = strtolower($position);
        $result = [];
        $input  = Factory::getApplication()->input;

        if (!empty($position) && MageBridgeTemplateHelper::allowPosition($position) == false) {
            return $result;
        }

        $modules = &static::load();

        $total = count($modules);
        for ($i = 0; $i < $total; $i++) {
            if ($modules[$i]->position == $position) {
                $result[] = &$modules[$i];
            }
        }

        if (count($result) == 0) {
            if ($input->getBool('tp') && ComponentHelper::getParams('com_templates')->get('template_positions_display')) {
                $result[0] = static::getModule('mod_' . $position);
                $result[0]->title = $position;
                $result[0]->content = $position;
                $result[0]->position = $position;
            }
        }

        return $result;
    }

    /**
     * Checks if a module is enabled. A given module will only be returned
     * if it meets the following criteria: it is enabled, it is assigned to
     * the current menu item or all items, and the user meets the access level
     * requirements.
     *
     * @param   string  $module  The module name
     *
     * @return  bool See description for conditions.
     *
     * @since   1.5
     */
    public static function isEnabled($module)
    {
        $result = static::getModule($module);

        return (!is_null($result) && $result->id !== 0);
    }

    /**
     * Render the module.
     *
     * @param   object  $module   A module object.
     * @param   array   $attribs  An array of attributes for the module (probably from the XML).
     *
     * @return  string  The HTML content of the module output.
     *
     * @since   1.5
     */
    public static function renderModule($module, $attribs = [])
    {
        static $chrome;

        if (is_object($module) && isset($module->position) && MageBridgeTemplateHelper::allowPosition($module->position) == false) {
            return null;
        }

        if (defined('JDEBUG')) {
            Profiler::getInstance('Application')->mark('beforeRenderModule ' . $module->module . ' (' . $module->title . ')');
        }

        /** @var \Joomla\CMS\Application\CMSApplication */
        $app = Factory::getApplication();

        // Record the scope.
        $scope = $app->scope;

        // Set scope to component name
        $app->scope = $module->module;

        // Get module parameters
        $params = new Registry();
        $params->loadString($module->params);

        // Get the template
        $template = $app->getTemplate();

        // Get module path
        $module->module = preg_replace('/[^A-Z0-9_\.-]/i', '', $module->module);
        $path = JPATH_BASE . '/modules/' . $module->module . '/' . $module->module . '.php';

        // Load the module
        if (file_exists($path)) {
            $lang = Factory::getLanguage();

            // 1.5 or Core then 1.6 3PD
            $lang->load($module->module, JPATH_BASE, null, false, true) ||
                $lang->load($module->module, dirname($path), null, false, true);

            $content = '';
            ob_start();
            include $path;
            $module->content = ob_get_contents() . $content;
            ob_end_clean();
        }

        // Load the module chrome functions
        if (!$chrome) {
            $chrome = [];
        }

        include_once JPATH_THEMES . '/system/html/modules.php';
        $chromePath = JPATH_THEMES . '/' . $template . '/html/modules.php';

        if (!isset($chrome[$chromePath])) {
            if (file_exists($chromePath)) {
                include_once $chromePath;
            }

            $chrome[$chromePath] = true;
        }

        // Check if the current module has a style param to override template module style
        $paramsChromeStyle = $params->get('style');
        if ($paramsChromeStyle) {
            $attribs['style'] = preg_replace('/^(system|' . $template . ')\-/i', '', $paramsChromeStyle);
        }

        // Make sure a style is set
        if (!isset($attribs['style'])) {
            $attribs['style'] = 'none';
        }

        // Dynamically add outline style
        if ($app->input->getBool('tp') && ComponentHelper::getParams('com_templates')->get('template_positions_display')) {
            $attribs['style'] .= ' outline';
        }

        foreach (explode(' ', $attribs['style']) as $style) {
            $chromeMethod = 'modChrome_' . $style;

            // Apply chrome and render module
            if (function_exists($chromeMethod)) {
                $module->style = $attribs['style'];

                ob_start();
                $chromeMethod($module, $params, $attribs);
                $module->content = ob_get_contents();
                ob_end_clean();
            }
        }

        // Revert the scope
        $app->scope = $scope;

        if (defined('JDEBUG')) {
            Profiler::getInstance('Application')->mark('afterRenderModule ' . $module->module . ' (' . $module->title . ')');
        }

        return $module->content;
    }

    /**
     * Get the path to a layout for a module
     *
     * @param   string  $module  The name of the module
     * @param   string  $layout  The name of the module layout. If alternative layout, in the form template:filename.
     *
     * @return  string  The path to the module layout
     *
     * @since   1.5
     */
    public static function getLayoutPath($module, $layout = 'default')
    {
        /** @var \Joomla\CMS\Application\CMSApplication */
        $app = Factory::getApplication();
        $template = $app->getTemplate();
        $defaultLayout = $layout;

        if (strpos($layout, ':') !== false) {
            // Get the template and file name from the string
            $temp = explode(':', $layout);
            $template = ($temp[0] == '_') ? $template : $temp[0];
            $layout = $temp[1];
            $defaultLayout = ($temp[1]) ? $temp[1] : 'default';
        }

        // Build the template and base path for the layout
        $tPath = JPATH_THEMES . '/' . $template . '/html/' . $module . '/' . $layout . '.php';
        $bPath = JPATH_BASE . '/modules/' . $module . '/tmpl/' . $defaultLayout . '.php';
        $dPath = JPATH_BASE . '/modules/' . $module . '/tmpl/default.php';

        // If the template has a layout override use it
        if (file_exists($tPath)) {
            return $tPath;
        } elseif (file_exists($bPath)) {
            return $bPath;
        } else {
            return $dPath;
        }
    }

    /**
     * Load published modules.
     *
     * @return  array
     *
     * @since   3.2
     */
    protected static function &load()
    {
        static $clean;

        if (isset($clean)) {
            return $clean;
        }

        /** @var \Joomla\CMS\Application\SiteApplication */
        $app = Factory::getApplication();
        $Itemid = $app->input->getInt('Itemid');
        $user = version_compare(JVERSION, '4.0.0', '<')
            ? Factory::getUser()
            : Factory::getApplication()->getIdentity();
        $groups = implode(',', $user->getAuthorisedViewLevels());
        $lang = Factory::getLanguage()->getTag();
        $clientId = (int) $app->getClientId();

        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params, mm.menuid')
            ->from('#__modules AS m')
            ->join('LEFT', '#__modules_menu AS mm ON mm.moduleid = m.id')
            ->where('m.published = 1')

            ->join('LEFT', '#__extensions AS e ON e.element = m.module AND e.client_id = m.client_id')
            ->where('e.enabled = 1');

        $date = Factory::getDate();
        $now = $date->toSql();
        $nullDate = $db->getNullDate();
        $query->where('(m.publish_up = ' . $db->quote($nullDate) . ' OR m.publish_up <= ' . $db->quote($now) . ')')
            ->where('(m.publish_down = ' . $db->quote($nullDate) . ' OR m.publish_down >= ' . $db->quote($now) . ')')

            ->where('m.access IN (' . $groups . ')')
            ->where('m.client_id = ' . $clientId)
            ->where('(mm.menuid = ' . (int) $Itemid . ' OR mm.menuid <= 0)');

        // Filter by language
        if ($app->isClient('site') && $app->getLanguageFilter()) {
            $query->where('m.language IN (' . $db->quote($lang) . ',' . $db->quote('*') . ')');
        }

        $query->order('m.position, m.ordering');

        // Set the query
        $db->setQuery($query);
        $clean = [];

        try {
            $modules = $db->loadObjectList();
        } catch (RuntimeException $e) {
            Log::add(Text::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $e->getMessage()), Log::WARNING, 'jerror');
            return $clean;
        }

        // Apply negative selections and eliminate duplicates
        $negId = $Itemid ? -(int) $Itemid : false;
        $dupes = [];
        for ($i = 0, $n = count($modules); $i < $n; $i++) {
            $module = &$modules[$i];

            // The module is excluded if there is an explicit prohibition
            $negHit = ($negId === (int) $module->menuid);

            if (isset($dupes[$module->id])) {
                // If this item has been excluded, keep the duplicate flag set,
                // but remove any item from the cleaned array.
                if ($negHit) {
                    unset($clean[$module->id]);
                }
                continue;
            }

            $dupes[$module->id] = true;

            // Only accept modules without explicit exclusions.
            if (!$negHit) {
                $module->name = substr($module->module, 4);
                $module->style = null;
                $module->position = strtolower($module->position);
                $clean[$module->id] = $module;
            }
        }

        unset($dupes);

        // Return to simple indexing that matches the query order.
        $clean = array_values($clean);

        return $clean;
    }

    /**
     * Module cache helper
     *
     * Caching modes:
     * To be set in XML:
     * 'static'	  One cache file for all pages with the same module parameters
     * 'oldstatic'   1.5 definition of module caching, one cache file for all pages
     *			   with the same module id and user aid,
     * 'itemid'	  Changes on itemid change, to be called from inside the module:
     * 'safeuri'	 Id created from $cacheparams->modeparams array,
     * 'id'		  Module sets own cache id's
     *
     * @param   object  $module		Module object
     * @param   object  $moduleparams  Module parameters
     * @param   object  $cacheparams   Module cache parameters - id or url parameters, depending on the module cache mode
     *
     * @return  string
     *
     * @see	 JFilterInput::clean()
     * @since   1.6
     */
    public static function moduleCache($module, $moduleparams, $cacheparams)
    {
        if (!isset($cacheparams->modeparams)) {
            $cacheparams->modeparams = null;
        }

        if (!isset($cacheparams->cachegroup)) {
            $cacheparams->cachegroup = $module->module;
        }

        $user = version_compare(JVERSION, '4.0.0', '<')
            ? Factory::getUser()
            : Factory::getApplication()->getIdentity();
        $cache = Factory::getCache($cacheparams->cachegroup, 'callback');
        $conf = Factory::getConfig();
        $app = Factory::getApplication();

        // Turn cache off for internal callers if parameters are set to off and for all logged in users
        if ($moduleparams->get('owncache', null) === '0' || $conf->get('caching') == 0 || $user->get('id')) {
            $cache->setCaching(false);
        }

        // Module cache is set in seconds, global cache in minutes, setLifeTime works in minutes
        $cache->setLifeTime($moduleparams->get('cache_time', $conf->get('cachetime') * 60) / 60);

        $wrkaroundoptions = ['nopathway' => 1, 'nohead' => 0, 'nomodules' => 1, 'modulemode' => 1, 'mergehead' => 1];

        $wrkarounds = true;
        $view_levels = md5(serialize($user->getAuthorisedViewLevels()));

        switch ($cacheparams->cachemode) {
            case 'id':
                $ret = $cache->get(
                    [$cacheparams->class, $cacheparams->method],
                    $cacheparams->methodparams,
                    $cacheparams->modeparams,
                    $wrkarounds,
                    $wrkaroundoptions
                );
                break;

            case 'safeuri':
                $secureid = null;
                if (is_array($cacheparams->modeparams)) {
                    $input = $app->input;
                    $safeuri = new stdClass();
                    foreach ($cacheparams->modeparams as $key => $value) {
                        // Use int filter for id/catid to clean out spamy slugs
                        $value = $input->get($key);
                        if (isset($value)) {
                            $noHtmlFilter = InputFilter::getInstance();
                            $safeuri->$key = $noHtmlFilter->clean($value, $value);
                        }
                    }
                }
                $secureid = md5(serialize([$safeuri, $cacheparams->method, $moduleparams]));
                $ret = $cache->get(
                    [$cacheparams->class, $cacheparams->method],
                    $cacheparams->methodparams,
                    $module->id . $view_levels . $secureid,
                    $wrkarounds,
                    $wrkaroundoptions
                );
                break;

            case 'static':
                $ret = $cache->get(
                    [
                        $cacheparams->class,
                        $cacheparams->method,
                    ],
                    $cacheparams->methodparams,
                    $module->module . md5(serialize($cacheparams->methodparams)),
                    $wrkarounds,
                    $wrkaroundoptions
                );
                break;

                // Provided for backward compatibility, not really useful.
            case 'oldstatic':
                $ret = $cache->get(
                    [$cacheparams->class, $cacheparams->method],
                    $cacheparams->methodparams,
                    $module->id . $view_levels,
                    $wrkarounds,
                    $wrkaroundoptions
                );
                break;

            case 'itemid':
            default:
                $ret = $cache->get(
                    [$cacheparams->class, $cacheparams->method],
                    $cacheparams->methodparams,
                    $module->id . $view_levels . $app->input->getInt('Itemid', null),
                    $wrkarounds,
                    $wrkaroundoptions
                );
                break;
        }

        return $ret;
    }
}
