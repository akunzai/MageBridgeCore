<?php

declare(strict_types=1);

namespace MageBridge\Plugin\MageBridgeStore\Falang\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\SubscriberInterface;
use Yireo\Helper\PathHelper;

/**
 * MageBridge Store Plugin - Falang.
 *
 * Dynamically loads a Magento store-scope based on the Falang language.
 *
 * @since 4.0.0
 */
class FalangStorePlugin extends CMSPlugin implements SubscriberInterface
{
    /**
     * Database object.
     */
    protected DatabaseInterface $db;

    /**
     * Application object.
     *
     * @var CMSApplication
     */
    protected $app;

    /**
     * Deprecated variable to migrate from the original connector-architecture to new Store Plugins.
     */
    protected string $connector_field = 'falang_language';

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onMageBridgeValidate' => 'onMageBridgeValidate',
            'onMageBridgeStorePrepareForm' => 'onMageBridgeStorePrepareForm',
            'onMageBridgeStoreConvertField' => 'onMageBridgeStoreConvertField',
        ];
    }

    /**
     * Constructor.
     *
     * @param array<string, mixed> $config Plugin configuration
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->loadLanguage();

        $app = Factory::getApplication();
        assert($app instanceof CMSApplication);
        $this->app = $app;
        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
    }

    /**
     * Event "onMageBridgeValidate".
     *
     * Validates if the current Falang language matches the configured store mapping.
     *
     * @param array<string, mixed>|null $actions Plugin actions configuration
     * @param object|null $condition Store condition object
     */
    public function onMageBridgeValidate(?array $actions = null, ?object $condition = null): bool
    {
        // Make sure this plugin is enabled
        if ($this->isEnabled() === false) {
            return false;
        }

        // Make sure to check upon the $actions array to see if it contains what we need
        if (empty($actions['falang_language'])) {
            return false;
        }

        // Fetch the current language
        /** @var Language $language */
        $language = $this->app->getLanguage();

        // Fetch the languages (requires Falang component)
        if (!class_exists('FalangManager')) {
            return false;
        }

        /** @var object $falangManager */
        $falangManager = \FalangManager::getInstance();

        if (!method_exists($falangManager, 'getActiveLanguages')) {
            return false;
        }

        /** @var array<int, object> $languages */
        $languages = $falangManager->getActiveLanguages();
        $languageCode = $this->app->getInput()->getCmd('lang', '');

        if (!empty($languages)) {
            foreach ($languages as $l) {
                $code = $l->code ?? '';
                $langCode = $l->lang_code ?? '';

                if ($language->getTag() === $code || $language->getTag() === $langCode) {
                    if (!empty($l->lang_code) && $l->lang_code === $actions['falang_language']) {
                        return true;
                    }

                    if (!empty($l->shortcode) && $l->shortcode === $actions['falang_language']) {
                        return true;
                    }

                    if (!empty($l->sef) && $l->sef === $actions['falang_language']) {
                        return true;
                    }
                }
            }
        }

        // Check if the condition applies
        if ($actions['falang_language'] === $languageCode) {
            return true;
        }

        return false;
    }

    /**
     * Method to manipulate the MageBridge Store Relation backend-form.
     *
     * @param Form $form The form to be altered
     * @param array<string, mixed>|object $data The associated data for the form
     */
    public function onMageBridgeStorePrepareForm(Form $form, $data): bool
    {
        // Check if this plugin can be used
        if ($this->isEnabled() === false) {
            return false;
        }

        // Add the plugin-form to main form
        $formFile = PathHelper::getSitePath() . '/plugins/magebridgestore/' . $this->_name . '/form/form.xml';
        if (file_exists($formFile)) {
            $form->loadFile($formFile, false);
        }

        // Convert data to array if it's an object
        $dataArray = is_array($data) ? $data : (array) $data;

        // Load the original values from the deprecated connector-architecture
        if ($this->connector_field !== '') {
            $pluginName = $this->_name;
            if (!empty($dataArray['connector']) && !empty($dataArray['connector_value']) && $pluginName === $dataArray['connector']) {
                $form->bind(['actions' => [$this->connector_field => $dataArray['connector_value']]]);
            }
        }

        return true;
    }

    /**
     * Method to convert legacy connector fields.
     *
     * @param object $connector The connector-row
     * @param array<string, mixed> $actions Reference to actions array
     */
    public function onMageBridgeStoreConvertField(object $connector, array &$actions): bool
    {
        // Check if this plugin can be used
        if ($this->isEnabled() === false) {
            return false;
        }

        // Load the original values from the deprecated connector-architecture
        if ($this->connector_field !== '') {
            $pluginName = $this->_name;
            $connectorName = $connector->connector ?? '';
            $connectorValue = $connector->connector_value ?? '';

            if ($connectorName !== '' && $connectorValue !== '' && $pluginName === $connectorName) {
                $actions = [$this->connector_field => $connectorValue];
            }
        }

        return true;
    }

    /**
     * Method to check whether this plugin is enabled or not.
     */
    protected function isEnabled(): bool
    {
        return is_dir(PathHelper::getSitePath() . '/components/com_falang');
    }
}
