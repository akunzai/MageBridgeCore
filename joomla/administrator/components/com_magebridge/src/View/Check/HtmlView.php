<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Check;

use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use MageBridge\Component\MageBridge\Administrator\Helper\View;
use MageBridge\Component\MageBridge\Administrator\View\BaseHtmlView;
use MageBridge\Component\MageBridge\Administrator\Model\CheckModel;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\Proxy\Proxy;
use RuntimeException;

class HtmlView extends BaseHtmlView
{
    protected bool $loadToolbar = false;

    protected array $checks = [];

    protected $form;

    protected string $url = '';

    protected string $host = '';

    public function display($tpl = null)
    {
        $layout = $this->input->getCmd('layout');

        $this->setMenu();

        if ($layout === 'browser') {
            $this->displayBrowser($tpl);

            return;
        }

        if ($layout === 'product') {
            $this->displayProduct($tpl);

            return;
        }

        if ($layout === 'result') {
            $this->displayResult($tpl);

            return;
        }

        $this->displayDefault($tpl);
    }

    public function displayDefault($tpl)
    {
        View::initialize('Check');

        ToolbarHelper::custom('refresh', 'refresh', '', 'Refresh', false);

        $model = $this->getModel();

        $this->checks = $model instanceof CheckModel ? $model->getChecks() : [];

        parent::display($tpl);
    }

    public function displayProduct($tpl)
    {
        $model = $this->getModel();

        if ($model instanceof CheckModel) {
            $model->setConfig('form_name', 'check_product');
        }

        $this->_viewParent = 'form';
        $this->form        = $model instanceof CheckModel ? $model->getForm() : null;

        View::initialize('PRODUCT_RELATION_TEST');

        ToolbarHelper::custom('check_product', 'refresh', '', 'Run', false);

        parent::display('product');
    }

    public function displayBrowser($tpl)
    {
        View::initialize('Internal Browse Test');

        ToolbarHelper::custom('refresh', 'refresh', '', 'Browse', false);

        $this->url  = ConfigModel::load('url') . 'magebridge.php';
        $this->host = (string) ConfigModel::load('host');

        parent::display('browser');
    }

    public function displayResult($tpl)
    {
        // Set plain text headers to prevent Joomla template rendering
        header('Content-Type: text/plain; charset=utf-8');

        $url  = ConfigModel::load('url') . 'magebridge.php';
        $host = (string) ConfigModel::load('host');
        $port = (int) ConfigModel::load('port');

        // Default to port 443 for HTTPS or 80 for HTTP if port is not configured
        if ($port === 0) {
            $port = str_starts_with($url, 'https://') ? 443 : 80;
        }

        try {
            if (!preg_match('/^([0-9\.]+)$/', $host)) {
                $host = preg_replace('/\:[0-9]+$/', '', $host) ?? $host;

                if (gethostbyname($host) === $host) {
                    echo 'ERROR: Failed to resolve hostname "' . $host . '" in DNS';
                    exit;
                }
            }

            if (@fsockopen($host, $port, $errno, $errmsg, 5) === false) {
                echo 'ERROR: Failed to open a connection to host "' . $host . '" on port "' . $port . '". Perhaps a firewall is in the way?';
                exit;
            }

            $responses   = [];
            $responses[] = $this->fetchContent('Basic bridge connection succeeded', $url, ['mbtest' => 1]);
            $responses[] = $this->fetchContent('API authentication succeeded', $url, ['mbauthtest' => 1]);

            echo implode("\n", $responses);
        } catch (RuntimeException $e) {
            echo $e->getMessage();
        }

        exit;
    }

    protected function fetchContent(string $label, string $url, array $params): string
    {
        $proxy = Proxy::getInstance();
        $proxy->setAllowRedirects(false);
        $content = $proxy->getRemote($url, $params, 'post');

        $proxyError = $proxy->getProxyError();

        if (!empty($proxyError)) {
            throw new RuntimeException('ERROR: Proxy error: ' . $proxyError);
        }

        $httpStatus = $proxy->getHttpStatus();

        if ($httpStatus !== 200) {
            throw new RuntimeException('ERROR: Encountered a HTTP Status ' . $httpStatus);
        }

        if ($content === '' || $content === null) {
            throw new RuntimeException('ERROR: Empty content');
        }

        if (preg_match('/<\/html>$/', $content)) {
            throw new RuntimeException('ERROR: Data contains HTML not JSON');
        }

        $data = json_decode($content, true);

        if (empty($data)) {
            throw new RuntimeException('ERROR: Failed to decode JSON');
        }

        if (!array_key_exists('meta', $data)) {
            throw new RuntimeException('ERROR: JSON response contains unknown data: ' . var_export($data, true));
        }

        return 'SUCCESS: ' . $label;
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getChecks(): array
    {
        return $this->checks;
    }

    public function getFormObject(): ?Form
    {
        return $this->form instanceof Form ? $this->form : null;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getHost(): string
    {
        return $this->host;
    }
}
