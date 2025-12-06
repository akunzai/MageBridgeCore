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
        $redirectUrl = $this->decodeRedirect($app->getInput()->getString('redirect'));

        if ($redirectUrl === '') {
            $redirectUrl = BridgeModel::getInstance()->getMagentoUrl();
        }

        $app->redirect($redirectUrl);

        $app->close();
    }

    public function logout(): void
    {
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $user = $app->getIdentity();
        $app->logout($user->get('id'));

        $redirectUrl = $this->decodeRedirect($app->getInput()->getString('redirect'));

        if ($redirectUrl === '') {
            $redirectUrl = BridgeModel::getInstance()->getMagentoUrl();
        }

        $app->redirect($redirectUrl);
        $app->close();
    }

    private function decodeRedirect(string $value): string
    {
        $decoded = base64_decode($value, true);

        return is_string($decoded) ? $decoded : '';
    }
}
