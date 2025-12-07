<?php

declare(strict_types=1);

namespace Yireo\Model\Trait;

defined('_JEXEC') or die();

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Form\FormHelper;
use Yireo\Helper\Helper;
use Yireo\Helper\PathHelper;

/**
 * Yireo Model Trait: Formable - allows models to have a form.
 */
trait Formable
{
    /**
     * Method to get a XML-based form.
     */
    public function getForm(array|object|null $data = null, bool $loadData = true): Form|false
    {
        $form = $this->loadForm();

        if (!$form instanceof Form) {
            return false;
        }

        if ($data === null && method_exists($this, 'getData')) {
            $data = $this->getData();
        }

        if ($loadData) {
            $form->bind(['item' => $data]);
        }

        $params = null;

        if (is_object($data) && isset($data->params)) {
            $params = $data->params;
        }

        if (is_array($data) && array_key_exists('params', $data)) {
            $params = $data['params'];
        }

        if ($params !== null && $params !== '') {
            if (is_string($params)) {
                $params = Helper::toRegistry($params);
            }

            if ($params instanceof \Joomla\Registry\Registry) {
                $params = $params->toArray();
            }

            if (is_array($params)) {
                $form->bind(['params' => $params]);
            }
        }

        return $form;
    }

    /**
     * Allow usage of this form.
     */
    protected function loadForm(): Form|false
    {
        // Do not continue if this is not the right backend
        if ($this->app->isClient('administrator') == false && $this->getConfig('frontend_form') == false) {
            return false;
        }

        // Do not continue if this is not a singular view
        if (method_exists($this, 'isSingular') && $this->isSingular() == false) {
            return false;
        }

        // Read the form from XML
        $xmlFile = $this->detectXmlFile();

        if (!file_exists($xmlFile)) {
            return false;
        }

        // Construct the form-object
        $form = $this->getFormFromXml($xmlFile);

        return $form;
    }

    /**
     * Get the form name.
     *
     * @return string
     */
    public function getFormName()
    {
        $formName = $this->getConfig('form_name');

        if (empty($formName)) {
            $formName = $this->getConfig('table_alias');
        }

        return $formName;
    }

    /**
     * Detect the XML file containing the form.
     */
    protected function detectXmlFile(): string
    {
        $option = $this->getOption();
        $formName = $this->getFormName();

        $paths = [
            PathHelper::getAdministratorPath() . '/components/' . $option . '/forms/' . $formName . '.xml',
            PathHelper::getAdministratorPath() . '/components/' . $option . '/models/' . $formName . '.xml',
            PathHelper::getSitePath() . '/components/' . $option . '/forms/' . $formName . '.xml',
            PathHelper::getSitePath() . '/components/' . $option . '/models/' . $formName . '.xml',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return '';
    }

    /**
     * Get the JForm object from an XML file.
     */
    protected function getFormFromXml(string $xmlFile): Form
    {
        // Register MageBridge custom field types namespace prefix
        // Field classes should be named like BooleanField, HttpauthField, etc.
        FormHelper::addFieldPrefix('MageBridge\\Component\\MageBridge\\Administrator\\Field');

        $formFactory = Factory::getContainer()->get(FormFactoryInterface::class);
        $form        = $formFactory->createForm('item', ['control' => 'jform']);
        $form->loadFile($xmlFile);

        return $form;
    }

    /**
     * Method to temporarily store an object in the current session.
     */
    public function saveTmpSession(array|object $data): void
    {
        /** @var CMSApplication $app */
        $app     = Factory::getApplication();
        $session = $app->getSession();
        $session->set($this->getConfig('option_id'), $data);
    }

    /**
     * Load a temporarily stored object from the current session.
     */
    public function loadTmpSession(): bool
    {
        /** @var CMSApplication $app */
        $app     = Factory::getApplication();
        $session = $app->getSession();
        $data    = $session->get($this->getConfig('option_id'));

        if ($data === null) {
            return false;
        }

        if (!is_array($data) && !is_object($data)) {
            return false;
        }

        if (empty($data)) {
            return false;
        }

        foreach ($data as $name => $value) {
            if (empty($value)) {
                continue;
            }

            if (is_array($this->data)) {
                $this->data[$name] = $value;
                continue;
            }

            if (is_object($this->data)) {
                $this->data->$name = $value;
            }
        }

        return true;
    }

    /**
     * Reset a temporarily stored object in the current session.
     */
    public function resetTmpSession(): void
    {
        /** @var CMSApplication $app */
        $app     = Factory::getApplication();
        $session = $app->getSession();
        $session->remove($this->getConfig('option_id'));
    }
}
