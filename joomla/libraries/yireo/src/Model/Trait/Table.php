<?php

declare(strict_types=1);

namespace Yireo\Model\Trait;

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;

/**
 * Yireo Model Trait: Table - allows models to have tables.
 */
trait Table
{
    /**
     * Boolean to skip table-detection.
     *
     * @var bool
     */
    protected $skip_table = true;

    /**
     * Database table object.
     *
     * @var \Yireo\Table\Table|null
     */
    protected $table;

    public function getSkipTable(): bool
    {
        return $this->skip_table;
    }

    public function setSkipTable(bool $skip_table): void
    {
        $this->skip_table = $skip_table;
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return $this->getConfig('table_alias');
    }

    /**
     * @param string $table_alias
     */
    public function setTableAlias($table_alias)
    {
        $this->setConfig('table_alias', $table_alias);
    }

    /**
     * @return bool
     */
    public function setTablePrefix($table_prefix = null)
    {
        // Set the database variables
        if ($this->getConfig('table_prefix_auto') === true) {
            $tablePrefix = $this->getConfig('component') . 'Table';
            $this->setConfig('table_prefix', $tablePrefix);

            return true;
        }

        return false;
    }

    /**
     * Override the default method to allow for skipping table creation.
     *
     * @param string $name
     * @param string $prefix
     * @param array $options
     *
     * @return \Yireo\Table\Table|null
     */
    public function getTable($name = '', $prefix = 'Table', $options = [])
    {
        if ($this->getConfig('skip_table') == true) {
            return null;
        }

        if (empty($name)) {
            $name = $this->getConfig('table_alias');
        }

        // First, try to load the table using the MVC Factory (Joomla 5 way)
        $table = $this->loadTableViaMVCFactory($name, $options);

        if ($table !== null) {
            return $table;
        }

        // Fallback: try to load the table directly using the namespace
        $table = $this->loadTableViaNamespace($name, $options);

        if ($table !== null) {
            return $table;
        }

        // Last resort: use parent method (legacy way)
        $tablePrefix = $this->getConfig('table_prefix');

        if (!empty($tablePrefix)) {
            $prefix = $tablePrefix;
        }

        // @phpstan-ignore-next-line - Joomla Table is compatible with Yireo Table
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Try to load the table using the MVC Factory.
     *
     * @return \Yireo\Table\Table|null
     */
    protected function loadTableViaMVCFactory(string $name, array $options = [])
    {
        try {
            $app = Factory::getApplication();
            $option = $this->getConfig('option') ?: $app->getInput()->getCmd('option');

            // Get the component's MVC factory from the container
            $container = Factory::getContainer();
            $factoryKey = 'MVC_Factory_' . str_replace(' ', '', ucwords(str_replace('_', ' ', substr($option, 4))));

            if ($container->has(MVCFactoryInterface::class)) {
                $factory = $container->get(MVCFactoryInterface::class);

                return $factory->createTable(ucfirst($name), 'Administrator', $options);
            }
        } catch (\Exception $e) {
            // Silently fail and try next method
        }

        return null;
    }

    /**
     * Try to load the table directly using the namespace.
     *
     * @return \Yireo\Table\Table|null
     */
    protected function loadTableViaNamespace(string $name, array $options = [])
    {
        $option = $this->getConfig('option') ?: 'com_magebridge';
        $componentName = str_replace(' ', '', ucwords(str_replace('_', ' ', substr($option, 4))));

        // Try different namespace patterns
        $namespaces = [
            // Joomla 5 standard namespace
            "MageBridge\\Component\\MageBridge\\Administrator\\Table\\" . ucfirst($name),
            // Generic pattern
            ucfirst($componentName) . "\\Component\\" . ucfirst($componentName) . "\\Administrator\\Table\\" . ucfirst($name),
        ];

        foreach ($namespaces as $className) {
            if (class_exists($className)) {
                $db = Factory::getContainer()->get(DatabaseInterface::class);

                return new $className($db);
            }
        }

        return null;
    }

    /**
     * Method to get the current primary key.
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->table->getKeyName();
    }
}
