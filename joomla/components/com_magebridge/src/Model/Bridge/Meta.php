<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Bridge;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;
use MageBridge\Component\MageBridge\Site\Helper\MageBridgeHelper;
use MageBridge\Component\MageBridge\Site\Helper\StoreHelper;
use MageBridge\Component\MageBridge\Site\Helper\TemplateHelper;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\Register;

final class Meta extends Segment
{
    private $metaData;

    public static function getInstance($name = null)
    {
        return parent::getInstance(self::class);
    }

    public function getResponseData()
    {
        return Register::getInstance()->getData('meta');
    }

    public function getRequestData()
    {
        if (!is_array($this->metaData) || $this->metaData === []) {
            $input       = $this->app->getInput();
            /** @var CMSApplication */
            $app = Factory::getApplication();
            $user        = $app->getIdentity();
            $uri         = Uri::getInstance();
            $config      = $app->getConfig();
            $storeHelper = StoreHelper::getInstance();

            $bridge   = BridgeModel::getInstance();
            $appType  = $storeHelper->getAppType();
            $appValue = $storeHelper->getAppValue();

            $arguments = [
                'api_session'                     => $bridge->getApiSession(),
                'api_user'                        => EncryptionHelper::encrypt(ConfigModel::load('api_user')),
                'api_key'                         => EncryptionHelper::encrypt(ConfigModel::load('api_key')),
                'api_url'                         => Uri::root() . 'component/magebridge/?controller=jsonrpc&task=call',
                'app'                             => $this->app->getClientId(),
                'app_type'                        => $appType,
                'app_value'                       => $appValue,
                'storeview'                       => ConfigModel::load('storeview'),
                'storegroup'                      => ConfigModel::load('storegroup'),
                'website'                         => ConfigModel::load('website'),
                'customer_group'                  => ConfigModel::load('customer_group'),
                'joomla_url'                      => $bridge->getJoomlaBridgeUrl(),
                'joomla_sef_url'                  => $bridge->getJoomlaBridgeSefUrl(),
                'joomla_sef_suffix'               => (int) UrlHelper::hasUrlSuffix(),
                'joomla_user_email'               => ($this->app->isClient('site') && !empty($user->email)) ? $user->email : null,
                'joomla_current_url'              => $uri->current(),
                'modify_url'                      => ConfigModel::load('modify_url'),
                'enforce_ssl'                     => ConfigModel::load('enforce_ssl'),
                'has_ssl'                         => (int) $uri->isSSL(),
                'payment_urls'                    => ConfigModel::load('payment_urls'),
                'enable_messages'                 => ConfigModel::load('enable_messages'),
                'joomla_session'                  => session_id(),
                'joomla_conf_caching'             => $config->get('caching', 60),
                'joomla_conf_lifetime'            => ($config->get('lifetime', 60) * 60),
                'magento_session'                 => $bridge->getMageSession(),
                'magento_persistent_session'      => $bridge->getMagentoPersistentSession(),
                'magento_user_allowed_save_cookie' => $_COOKIE['user_allowed_save_cookie'] ?? null,
                'request_uri'                     => UrlHelper::getRequest(),
                'request_id'                      => md5(Uri::current() . serialize($input->get->getArray())),
                'post'                            => !empty($_POST) ? $_POST : null,
                'http_referer'                    => $bridge->getHttpReferer(),
                'http_host'                       => $uri->toString(['host']),
                'user_agent'                      => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'remote_addr'                     => $_SERVER['REMOTE_ADDR'] ?? '',
                'debug'                           => (int) DebugModel::isDebug(),
                'debug_level'                     => ConfigModel::load('debug_level'),
                'debug_display_errors'            => ConfigModel::load('debug_display_errors'),
                'protocol'                        => ConfigModel::load('protocol'),
                'state'                           => 'initializing',
                'ajax'                            => (int) $bridge->isAjax(),
                'disable_css'                     => MageBridgeHelper::getDisableCss(),
                'disable_js'                      => MageBridgeHelper::getDisableJs(),
            ];

            $arguments['theme'] = TemplateHelper::isMobile()
                ? ConfigModel::load('mobile_magento_theme')
                : ConfigModel::load('magento_theme');

            foreach ($arguments as $name => $value) {
                if (is_string($value)) {
                    $arguments[$name] = EncryptionHelper::base64_encode($value);
                }
            }

            $this->metaData = $arguments;
        }

        return $this->metaData;
    }

    public function reset(): void
    {
        $this->metaData = null;
    }
}
