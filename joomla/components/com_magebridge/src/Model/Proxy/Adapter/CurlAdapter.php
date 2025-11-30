<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Proxy\Adapter;

defined('_JEXEC') or die;

use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\Proxy\Proxy;

final class CurlAdapter
{
    private ?string $url = null;

    private ?Proxy $proxy = null;

    private $data = null;

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function setProxy(?Proxy $proxy): void
    {
        $this->proxy = $proxy;
    }

    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * Send HTTP request using cURL.
     *
     * @return mixed
     */
    public function send()
    {
        if (empty($this->url)) {
            DebugModel::getInstance()->error('Adapter URL is empty');
            return false;
        }

        if (!function_exists('curl_init')) {
            DebugModel::getInstance()->error('cURL is not available');
            return false;
        }

        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int) ConfigModel::load('api_timeout', 15));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

        // Set POST data if provided
        // Each segment in $this->data should already be JSON encoded by encodeRequest()
        if (!empty($this->data) && is_array($this->data)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
        }

        // SSL options
        $ssl_noverify = (bool) ConfigModel::load('api_ssl_noverify', false);
        if ($ssl_noverify) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        // HTTP authentication (for proxy/firewall, not for MageBridge API)
        $http_authtype = (string) ConfigModel::load('http_authtype');
        $http_user = (string) ConfigModel::load('http_user');
        $http_password = (string) ConfigModel::load('http_password');
        if (!empty($http_authtype) && !empty($http_user)) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $http_user . ':' . $http_password);
        }

        // Execute request
        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            DebugModel::getInstance()->error('cURL error: ' . $error);
            return false;
        }

        // Get header size and split response
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        curl_close($ch);

        // Store headers and body in proxy
        if ($this->proxy !== null) {
            $this->proxy->setHead(['headers' => $headers]);
            $this->proxy->setBody($body);
        }

        return $body;
    }
}
