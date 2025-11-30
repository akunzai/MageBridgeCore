<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Input\Input;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\User\SsoModel;
use RuntimeException;

class DisplayController extends BaseController
{
    protected $default_view = 'root';

    /**
     * Cached bridge instance.
     *
     * @var BridgeModel
     */
    private $bridge;

    public function __construct(array $config = [], ?MVCFactoryInterface $factory = null, ?CMSApplicationInterface $app = null, ?Input $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        $this->app    = $app ?: Factory::getApplication();
        $this->bridge = BridgeModel::getInstance();

        $this->registerTask('switch', 'switchStores');
        $this->registerTask('login', 'ssoCheck');
        $this->registerTask('logout', 'ssoCheck');

        $this->guardPostRequest();
        $this->handleCustomerAddressDelete();
    }

    public function display($cachable = false, $urlparams = []): BaseController
    {
        if ($this->bridge->isOffline()) {
            $this->input->set('view', 'offline');
            $this->input->set('layout', 'default');
        }

        if (!$this->input->get('view')) {
            $this->input->set('view', $this->default_view);
        }

        $request = UrlHelper::getRequest() ?? '';

        if ($request === 'customer/account/logout') {
            /** @var CMSApplication $app */
            $app = Factory::getApplication();
            $app->getSession()->destroy();
        }

        $backend = ConfigModel::load('backend');

        if (!empty($backend) && $request !== '' && str_starts_with($request, (string) $backend)) {
            $request = str_replace($backend, '', $request);
            $url     = $this->bridge->getMagentoAdminUrl($request);
            $this->setRedirect($url);

            return $this;
        }
        if (
            $this->input->get('view') === 'catalog'
            && !in_array($this->input->get('layout'), ['product', 'category', 'addtocart'], true)
        ) {
            $url = UrlHelper::route('/');
            $this->setRedirect($url);

            return $this;
        }

        return parent::display($cachable, $urlparams);
    }

    public function ssoCheck(): void
    {
        $user = Factory::getApplication()->getIdentity();

        if (!$user->guest) {
            SsoModel::getInstance()->checkSSOLogin();
            $this->app->close();
        }

        $this->setRedirect(Uri::base());
    }

    public function proxy(): void
    {
        $url = (string) $this->input->getString('url');

        if ($url === '') {
            $this->app->close();

            return;
        }

        echo file_get_contents($this->bridge->getMagentoUrl() . $url);
        $this->app->close();
    }

    public function switchStores(): void
    {
        $store = (string) $this->input->getString('magebridge_store');
        /** @var CMSApplication $app */
        $app = Factory::getApplication();

        if ($store !== '' && preg_match('/(g|v):(.*)/', $store, $match)) {
            if ($match[1] === 'v') {
                $app->setUserState('magebridge.store.type', 'store');
                $app->setUserState('magebridge.store.name', $match[2]);
            }

            if ($match[1] === 'g') {
                $app->setUserState('magebridge.store.type', 'group');
                $app->setUserState('magebridge.store.name', $match[2]);
            }
        }

        $redirect = (string) $app->getInput()->getString('redirect');
        $app->redirect($redirect);
        $app->close();
    }

    private function guardPostRequest(): void
    {
        $post = $this->app->getInput()->post->getArray();

        if (!$this->shouldValidatePost() || empty($post)) {
            return;
        }

        if (!Session::checkToken()) {
            $this->forbidden('Invalid token');
        }

        $referer = $this->getHttpReferer();

        if ($referer === '') {
            $this->returnToRequestUri();
        }

        if (!preg_match($this->getHostPattern(), $referer)) {
            $this->returnToRequestUri();
        }
    }

    private function handleCustomerAddressDelete(): void
    {
        $uri = Uri::current();

        if (!str_contains($uri, '/customer/address/delete')) {
            return;
        }

        $referer = $this->getHttpReferer();

        if ($referer === '' || !preg_match($this->getHostPattern(), $referer)) {
            $this->returnToRequestUri();
        }
    }

    private function getHttpReferer(): string
    {
        return isset($_SERVER['HTTP_REFERER']) ? trim((string) $_SERVER['HTTP_REFERER']) : '';
    }

    private function getHttpHost(): string
    {
        return isset($_SERVER['HTTP_HOST']) ? trim((string) $_SERVER['HTTP_HOST']) : '';
    }

    private function shouldValidatePost(): bool
    {
        $uri = Uri::current();
        $checkPaths = ['customer', 'address', 'cart'];

        foreach ($checkPaths as $checkPath) {
            if (str_contains($uri, '/' . $checkPath . '/')) {
                return true;
            }
        }

        return false;
    }

    private function returnToRequestUri(): void
    {
        /** @var CMSApplication $app */
        $app = Factory::getApplication();

        $app->redirect(Uri::current());
        $app->close();
    }

    private function forbidden(string $message = 'Access denied'): void
    {
        throw new RuntimeException($message, 403);
    }

    private function getHostPattern(): string
    {
        $host = $this->getHttpHost();

        return '/^(https?:\\/\\/)' . preg_quote($host, '/') . '\//i';
    }
}
