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
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\User\SsoModel;

class SsoController extends BaseController
{
    public function __construct(
        array $config = [],
        ?MVCFactoryInterface $factory = null,
        ?CMSApplicationInterface $app = null,
        ?Input $input = null
    ) {
        parent::__construct($config, $factory, $app, $input);
    }

    public function login(): void
    {
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $user = $app->getIdentity();
        $app->login($user->get('id'));
        $redirectUrl = SsoModel::resolveRedirectUrl(
            SsoModel::decodeRedirect($app->getInput()->getString('redirect')),
            (string) BridgeModel::getInstance()->getMagentoUrl()
        );

        $app->redirect($redirectUrl);

        $app->close();
    }

    public function logout(): void
    {
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $user = $app->getIdentity();
        $app->logout($user->get('id'));

        $redirectUrl = SsoModel::resolveRedirectUrl(
            SsoModel::decodeRedirect($app->getInput()->getString('redirect')),
            (string) BridgeModel::getInstance()->getMagentoUrl()
        );

        $app->redirect($redirectUrl);
        $app->close();
    }
}
