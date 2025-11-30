<?php

declare(strict_types=1);

namespace MageBridge\Plugin\MageBridge\MageBridge\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\Event\SubscriberInterface;
use MageBridge\Component\MageBridge\Site\Library\Plugin;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Model\Bridge\Meta;
use MageBridge\Component\MageBridge\Site\Library\MageBridge as MageBridgeLibrary;

/**
 * MageBridge MageBridge Plugin.
 *
 * @since  3.0.0
 */
class MageBridgePlugin extends Plugin implements SubscriberInterface
{
    /**
     * @var CMSApplication
     */
    protected $app;

    /**
     * @var BridgeModel
     */
    protected $bridge;

    /**
     * Returns an array of events this subscriber will listen to.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeDisplayBlock' => 'onBeforeDisplayBlock',
            'onBeforeBuildMageBridge' => 'onBeforeBuildMageBridge',
            'onAfterBuildMageBridge' => 'onAfterBuildMageBridge',
        ];
    }

    /**
     * Constructor.
     *
     * @param mixed $subject Event dispatcher
     * @param array $config Plugin configuration
     */
    public function __construct(&$subject, array $config = [])
    {
        parent::__construct($subject, $config);
        $app = Factory::getApplication();
        assert($app instanceof CMSApplication);
        $this->app = $app;
        $this->bridge = BridgeModel::getInstance();
    }

    /**
     * Event onBeforeDisplayBlock.
     *
     * @param string $block_name
     * @param mixed $arguments
     * @param string $block_data
     */
    public function onBeforeDisplayBlock(&$block_name, $arguments, &$block_data)
    {
    }

    /**
     * Event onBeforeBuildMageBridge.
     */
    public function onBeforeBuildMageBridge()
    {
        // Get the current Magento request
        $request = UrlHelper::getRequest() ?? '';

        // Skip if no request (e.g. in admin backend)
        if ($request === '') {
            return;
        }

        // Check for the logout-page
        if ($request === 'customer/account/logoutSuccess') {
            $this->app->logout();
        }

        // When visiting the checkout/cart/add URL without a valid session, the action will fail because the session does not exist yet
        // The following workaround makes sure we first redirect to another page (to initialize the session) after which we can add the product
        if (preg_match('/checkout\/cart\/add\//', $request) && !preg_match('/redirect=1/', $request)) {
            $session = $this->bridge->getMageSession(); // Check for the Magento session-key stored in the Joomla! session

            // Session is NOT yet initialized, therefor addtocart is not working yet either
            if (empty($session) && !empty($_COOKIE)) {
                // Redirect the client to an intermediate page to properly initialize the session
                $this->bridge->setHttpReferer(UrlHelper::route($request . '?redirect=1'));
                UrlHelper::setRequest('magebridge/redirect/index/url/' . base64_encode($request));
                Meta::getInstance()->reset();
            }
        }
    }

    /**
     * Event onAfterBuildMageBridge.
     */
    public function onAfterBuildMageBridge()
    {
        // Perform actions on the frontend
        if ($this->app->isClient('site')) {
            $this->doDelayedRedirect();
            $this->doDelayedLogin();
        }
    }

    /**
     * Perform a delayed redirect.
     */
    private function doDelayedRedirect()
    {
        $redirectUrl = $this->bridge->getSessionData('redirect_url');

        if (!empty($redirectUrl)) {
            $redirectUrl = UrlHelper::route($redirectUrl);
            $this->app->redirect($redirectUrl);
            $this->app->close();
        }
    }

    /**
     * Perform a delayed login.
     */
    private function doDelayedLogin()
    {
        $userEmail = $this->bridge->getSessionData('customer/email');
        $userId = $this->bridge->getSessionData('customer/joomla_id');
        $userModel = MageBridgeLibrary::getUser();

        return $userModel->postlogin($userEmail, $userId);
    }
}
