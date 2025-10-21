<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Controller;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use Yireo\Helper\Helper;

class ConfigController extends BaseController
{
    /**
     * Constructor.
     *
     * @param CMSApplicationInterface|null $app application object
     * @param Input|null $input input object
     */
    public function __construct(
        array $config = [],
        ?MVCFactoryInterface $factory = null,
        ?CMSApplicationInterface $app = null,
        ?Input $input = null
    ) {
        parent::__construct($config, $factory, $app, $input);

        // Load Yireo library language file
        Helper::loadLanguageFile();
    }

    /**
     * Cancels the configuration form and returns to dashboard.
     */
    public function cancel(): void
    {
        $this->setRedirect(Route::_('index.php?option=com_magebridge', false));
    }

    /**
     * Saves configuration and returns to dashboard.
     */
    public function save(): bool
    {
        if (!$this->validateRequest()) {
            return false;
        }

        $result = $this->processStore();
        $this->setRedirect('index.php?option=com_magebridge', $result['message'], $result['type']);

        return $result['success'];
    }

    /**
     * Saves configuration and stays on configuration view.
     */
    public function apply(): bool
    {
        if (!$this->validateRequest()) {
            return false;
        }

        $result = $this->processStore();
        $this->setRedirect('index.php?option=com_magebridge&view=config', $result['message'], $result['type']);

        return $result['success'];
    }

    /**
     * Stores configuration and redirects back to configuration view.
     *
     * @param array $data optional data override
     */
    public function store($data = []): bool
    {
        if (!$this->validateRequest()) {
            return false;
        }

        $result = $this->processStore($data);
        $this->setRedirect('index.php?option=com_magebridge&view=config', $result['message'], $result['type']);

        return $result['success'];
    }

    /**
     * Shows the import layout.
     */
    public function import(): void
    {
        // Redirect to import layout
        $this->setRedirect(Route::_('index.php?option=com_magebridge&view=config&layout=import', false));
    }

    /**
     * Exports configuration to XML download.
     */
    public function export(): void
    {
        if (!$this->validateRequest(false, false)) {
            // ValidateRequest already set redirect when needed.
            return;
        }

        $config = ConfigModel::load();
        $date   = date('Ymd');
        $host   = str_replace('.', '_', $_SERVER['HTTP_HOST'] ?? 'localhost');
        $filename = 'magebridge-joomla-' . $host . '-' . $date . '.xml';
        $output   = $this->getOutput($config);

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Length: ' . strlen((string) $output));
        header('Content-type: application/xml');
        header('Content-Disposition: attachment; filename=' . $filename);
        echo $output;

        $this->app->close();
    }

    /**
     * Imports configuration from uploaded XML file.
     */
    public function upload(): bool
    {
        if (!$this->validateRequest()) {
            return false;
        }

        $upload = $this->input->get('xml', null, 'files');

        if (!$this->isValidUpload($upload)) {
            $this->setRedirect(
                'index.php?option=com_magebridge&view=config&task=import',
                Text::_('File upload failed on system level'),
                'error'
            );

            return false;
        }

        $xmlString = @file_get_contents($upload['tmp_name']);

        if (empty($xmlString)) {
            $this->setRedirect(
                'index.php?option=com_magebridge&view=config&task=import',
                Text::_('Empty file upload'),
                'error'
            );

            return false;
        }

        $xml = @simplexml_load_string($xmlString);

        if (!$xml) {
            $this->setRedirect(
                'index.php?option=com_magebridge&view=config&task=import',
                Text::_('Invalid XML-configuration'),
                'error'
            );

            return false;
        }

        $config = [];

        foreach ($xml->children() as $parameter) {
            $name  = (string) $parameter->name;
            $value = (string) $parameter->value;

            if ($name !== '') {
                $config[$name] = $value;
            }
        }

        if (empty($config)) {
            $this->setRedirect(
                'index.php?option=com_magebridge&view=config&task=import',
                Text::_('Nothing to import'),
                'error'
            );

            return false;
        }

        ConfigModel::getSingleton()->store($config);
        $this->setRedirect('index.php?option=com_magebridge&view=config', Text::_('Imported configuration successfully'));

        return true;
    }

    /**
     * Processes storing of configuration data.
     *
     * @param array $data Optional data override
     *
     * @return array{success:bool,message:string,type:string}
     */
    private function processStore(array $data = []): array
    {
        $post = !empty($data) ? $data : $this->input->post->getArray();
        $post = $this->fixPost($post);

        /** @var ConfigModel|false $model */
        $model = $this->getModel(
            'Config',
            'MageBridge\\Component\\MageBridge\\Administrator\\Model\\',
            ['ignore_request' => true]
        );

        // Fallback to singleton if MVCFactory fails
        if ($model === false) {
            $model = ConfigModel::getSingleton();
        }

        try {
            $model->store($post);
            $message = sprintf(Text::_('LIB_YIREO_CONTROLLER_ITEM_SAVED'), Text::_('COM_MAGEBRIDGE_VIEW_CONFIG'));

            return ['success' => true, 'message' => $message, 'type' => 'message'];
        } catch (Exception $e) {
            $message = sprintf(Text::_('LIB_YIREO_CONTROLLER_ITEM_NOT_SAVED'), Text::_('COM_MAGEBRIDGE_VIEW_CONFIG'));
            $error   = $e->getMessage();

            if (!empty($error)) {
                $message .= ': ' . $error;
            }

            return ['success' => false, 'message' => $message, 'type' => 'error'];
        }
    }

    /**
     * Normalises posted configuration data.
     *
     * Handles both legacy format ($post['config']) and Joomla form format ($post['jform']['config']).
     *
     * @param array $post Posted configuration data
     */
    private function fixPost(array $post): array
    {
        // Extract config array from jform if present (Joomla form format: jform[config][field])
        if (isset($post['jform']['config']) && is_array($post['jform']['config'])) {
            foreach ($post['jform']['config'] as $name => $value) {
                $post[$name] = $value;
            }

            unset($post['jform']);
        }

        // Also handle legacy format (config[field])
        if (isset($post['config']) && is_array($post['config'])) {
            foreach ($post['config'] as $name => $value) {
                $post[$name] = $value;
            }

            unset($post['config']);
        }

        // Get raw values for sensitive fields (passwords may contain special chars)
        $rawApiKey = $this->input->post->get('api_key', '', 'raw');
        $rawApiUser = $this->input->post->get('api_user', '', 'raw');

        // Also check jform format for raw values
        $jform = $this->input->post->get('jform', [], 'array');
        if (isset($jform['config']['api_key']) && $rawApiKey === '') {
            $rawApiKey = $jform['config']['api_key'];
        }
        if (isset($jform['config']['api_user']) && $rawApiUser === '') {
            $rawApiUser = $jform['config']['api_user'];
        }

        // Override with raw values if they exist
        if ($rawApiKey !== '') {
            $post['api_key'] = $rawApiKey;
        }
        if ($rawApiUser !== '') {
            $post['api_user'] = $rawApiUser;
        }

        return $post;
    }

    /**
     * Builds XML output from configuration.
     *
     * @param array $config configuration values
     */
    private function getOutput($config): string
    {
        $xml = '';

        if (!empty($config)) {
            $xml .= "<configuration>\n";

            foreach ($config as $c) {
                $xml .= "\t<parameter>\n";
                $xml .= "\t\t<id>" . $c['id'] . "</id>\n";
                $xml .= "\t\t<name>" . $c['name'] . "</name>\n";
                $xml .= "\t\t<value><![CDATA[" . $c['value'] . "]]></value>\n";
                $xml .= "\t</parameter>\n";
            }

            $xml .= "</configuration>\n";
        }

        return $xml;
    }

    /**
     * Validates upload structure.
     *
     * @param array|null $upload upload data
     */
    private function isValidUpload($upload): bool
    {
        if (empty($upload)) {
            return false;
        }

        return !empty($upload['name']) && !empty($upload['tmp_name']) && !empty($upload['size']);
    }

    /**
     * Validates request security.
     *
     * @param bool $checkDemo whether to disallow demo users
     */
    private function validateRequest(bool $checkDemo = true, bool $checkToken = true): bool
    {
        if ($checkToken && Session::checkToken('post') === false && Session::checkToken('get') === false) {
            $this->setRedirect('index.php?option=com_magebridge&view=home', Text::_('JINVALID_TOKEN'), 'error');

            return false;
        }

        if ($checkDemo && \MageBridge\Component\MageBridge\Administrator\Helper\Acl::isDemo()) {
            $this->setRedirect('index.php?option=com_magebridge&view=config', Text::_('LIB_YIREO_CONTROLLER_DEMO_NO_ACTION'), 'warning');

            return false;
        }

        return true;
    }
}
