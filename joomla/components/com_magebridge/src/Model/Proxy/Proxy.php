<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Proxy;

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Crypt\Cipher\SodiumCipher;
use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Helper\MageBridgeHelper;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;

final class Proxy extends AbstractProxy
{
    public array $rawheaders = [];

    protected array $head = [];

    protected string $body = '';

    protected string $data = '';

    protected bool $redirect = false;

    protected bool $allow_redirects = true;

    protected ?string $redirectUrl = null;

    protected function encodeData($data)
    {
        if (empty($data)) {
            return $data;
        }

        if (is_array($data)) {
            foreach ($data as $index => $segment) {
                if (empty($segment['data'])) {
                    $data[$index] = $this->encode($segment);
                }
            }
        }

        return $data;
    }

    protected function isNonBridgeOutput($response): bool
    {
        if (!empty($this->head['headers']) && preg_match('/Content-Type: application\/magebridge/i', $this->head['headers'])) {
            return false;
        }

        if ($this->bridge->isAjax()) {
            return true;
        }

        if ($this->isValidResponse($response) === false) {
            $this->debug->notice('Empty decoded response suggests non-bridge output');

            return true;
        }

        if ($this->isContentTypeHtml() === false) {
            $this->debug->trace('Detecting non-HTML output in HTTP headers', $this->head['headers']);

            return true;
        }

        return false;
    }

    protected function isContentTypeHtml(): bool
    {
        if (!empty($this->head['headers']) && preg_match('/Content-Type: (application|text)\/(xml|javascript|json|octetstream|pdf|x-pdf)/i', $this->head['headers'])) {
            return false;
        }

        return true;
    }

    protected function sendDirectOutputUrlResponse($response): void
    {
        $this->spoofHeaders($response);

        header('Content-Encoding: none');
        echo $response;

        $this->app->close();
    }

    protected function matchDirectOutputUrls(): bool
    {
        $directOutputUrls   = MageBridgeHelper::csvToArray(ConfigModel::load('direct_output'));
        $directOutputUrls[] = 'checkout/onepage/getAdditional';

        if (!empty($directOutputUrls)) {
            $currentUrl = UrlHelper::getRequest();

            foreach ($directOutputUrls as $directOutputUrl) {
                $directOutputUrl = trim($directOutputUrl);

                if (!empty($directOutputUrl) && str_contains($currentUrl, $directOutputUrl)) {
                    $this->debug->trace('Detecting non-bridge output through MageBridge configuration', $directOutputUrl);

                    return true;
                }
            }
        }

        return false;
    }

    protected function getContentTypeFromHeader(): ?string
    {
        if (!preg_match('/Content-Type: (.*)/i', $this->head['headers'] ?? '', $match)) {
            return null;
        }

        return strtolower(trim($match[1]));
    }

    protected function convertUrl($url)
    {
        if (!preg_match('/^index\.php\?option=com/', $url)) {
            return null;
        }

        $newUrl = MageBridgeHelper::filterUrl($url);

        if (empty($newUrl)) {
            return null;
        }

        return Uri::root() . preg_replace('/^index\.php\?/', '', $newUrl);
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl($redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function setAllowRedirects($allowRedirects): void
    {
        $this->allow_redirects = (bool) $allowRedirects;
    }

    public function getAllowRedirects(): bool
    {
        return $this->allow_redirects;
    }

    public function setRedirect($redirect): void
    {
        $this->redirect = (bool) $redirect;
    }

    public function getRedirect(): bool
    {
        return $this->redirect;
    }

    public function setHead($head): void
    {
        $this->head = $head;
    }

    public function getHead(): array
    {
        return $this->head;
    }

    public function setBody($body): void
    {
        $this->body = (string) $body;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setData($data): void
    {
        $this->data = (string) $data;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function reset(): void
    {
        $this->rawheaders = [];
        $this->head       = [];
        $this->body       = '';
        $this->data       = '';
        $this->redirect   = false;
        $this->redirectUrl = null;
    }

    public function encodeRequest($data = null)
    {
        return $this->encodeData($data);
    }

    public function encodeResponse($data = null)
    {
        if (empty($data)) {
            return $data;
        }

        if (is_array($data)) {
            foreach ($data as $index => $segment) {
                if (!empty($segment['data'])) {
                    $data[$index]['data'] = json_encode($segment['data']);
                }
            }
        }

        return $data;
    }

    public function decodeResponse($data = null)
    {
        if (empty($data)) {
            return $data;
        }

        if (!is_array($data)) {
            $decoded = $this->decode($data);

            if ($decoded === false) {
                return $data;
            }

            $data = $decoded;
        }

        if (is_array($data)) {
            foreach ($data as $index => $segment) {
                if (!empty($segment['data']) && is_string($segment['data'])) {
                    $decoded = json_decode($segment['data'], true);

                    if ($decoded !== false && $decoded !== $segment['data']) {
                        $data[$index]['data'] = $decoded;
                    }
                }
            }
        }

        return $data;
    }

    public function fetch($force = false)
    {
        if ($this->init === self::CONNECTION_SUCCESS && $force === false) {
            return true;
        }

        $url = $this->bridge->getMagentoBridgeUrl();

        if (empty($url)) {
            $this->debug->error('Magento bridge URL is empty');
            $this->init = self::CONNECTION_ERROR;

            return false;
        }

        $adapter = $this->helper->getAdapter();

        if ($adapter === null) {
            $this->debug->error('No proxy adapter available');
            $this->init = self::CONNECTION_ERROR;

            return false;
        }

        $this->init = self::CONNECTION_FALSE;

        $adapter->setUrl($url);
        $adapter->setProxy($this);

        $data = $this->bridge->getRegister()->getData();
        $data = $this->encodeRequest($data);

        $adapter->setData($data);
        $response = $adapter->send();

        if ($response === false) {
            $this->init = self::CONNECTION_ERROR;

            return false;
        }

        $this->setData($response);
        $response = $this->decodeResponse($response);

        if ($this->isNonBridgeOutput($response)) {
            if ($this->matchDirectOutputUrls()) {
                $this->sendDirectOutputUrlResponse($response);

                return true;
            }

            $this->debug->warning('Proxy returned non-bridge output');
            $this->init = self::CONNECTION_ERROR;

            return false;
        }

        $this->bridge->store($response);

        $this->init = self::CONNECTION_SUCCESS;

        return true;
    }

    public function isValidResponse($response): bool
    {
        return !empty($response);
    }

    public function spoofHeaders($response): void
    {
        if (empty($this->head['headers'])) {
            return;
        }

        $headers = explode("\n", $this->head['headers']);

        foreach ($headers as $header) {
            if (!preg_match('/^Content-Type:/i', $header)) {
                continue;
            }

            header($header);
        }
    }

    public function getEncryptionCipher()
    {
        if (class_exists(SodiumCipher::class)) {
            return new SodiumCipher();
        }

        return new Crypt();
    }

    public function encrypt($string)
    {
        $cipher = $this->getEncryptionCipher();
        $key    = ConfigModel::load('encryption_key');

        if (empty($key)) {
            $key = ApplicationHelper::getHash('magebridge');
        }

        return $cipher->encrypt($string, $key);
    }

    public function decrypt($string)
    {
        $cipher = $this->getEncryptionCipher();
        $key    = ConfigModel::load('encryption_key');

        if (empty($key)) {
            $key = ApplicationHelper::getHash('magebridge');
        }

        return $cipher->decrypt($string, $key);
    }

    public function handleRedirect($response): bool
    {
        if ($this->redirect !== true || $this->allow_redirects !== true) {
            return false;
        }

        $this->setRedirect(false);

        if (!empty($this->redirectUrl)) {
            $redirectUrl = $this->redirectUrl;
        } elseif (!empty($response['meta']['redirect'])) {
            $redirectUrl = $response['meta']['redirect'];
        } else {
            $redirectUrl = null;
        }

        if ($redirectUrl === null) {
            return false;
        }

        $this->app->redirect($redirectUrl);

        return true;
    }

    public function handleMessages(): bool
    {
        $messages = $this->bridge->getMessages();

        if (empty($messages)) {
            return false;
        }

        foreach ($messages as $message) {
            if (empty($message['text'])) {
                continue;
            }

            $type = $message['type'] ?? 'message';
            $this->app->enqueueMessage($message['text'], $type);
        }

        return true;
    }

    public function handleErrors(): bool
    {
        $errors = $this->bridge->getErrors();

        if (empty($errors)) {
            return false;
        }

        foreach ($errors as $error) {
            $message = $error['message'] ?? Text::_('Unknown error');
            $this->app->enqueueMessage($message, 'error');
        }

        return true;
    }
}
