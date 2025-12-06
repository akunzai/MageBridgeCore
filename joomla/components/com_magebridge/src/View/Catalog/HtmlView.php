<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\View\Catalog;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;
use MageBridge\Component\MageBridge\Site\Helper\MageBridgeHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\View\BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $bridge = BridgeModel::getInstance();

        $layout = $this->getLayout();
        $params = MageBridgeHelper::getParams();

        $request = $params->get('request', false) ?: UrlHelper::getRequest();
        $prefix  = preg_replace('/\?(.*)/', '', $request);
        $suffix  = preg_replace('/(.*)\?/', '', $request);

        if (is_numeric($prefix)) {
            $request = UrlHelper::getLayoutUrl($layout, $prefix);

            if (!empty($request)) {
                $request .= '?' . $suffix;
            }
        } else {
            if ($layout === 'product') {
                $suffix = $bridge->getSessionData('catalog/seo/product_url_suffix');
            } elseif ($layout === 'category') {
                $suffix = $bridge->getSessionData('catalog/seo/category_url_suffix');
            }

            if (!empty($suffix) && !preg_match('/' . preg_quote((string) $suffix, '/') . '$/', $request)) {
                $request .= $suffix;
            }
        }

        /** @var CMSApplication */
        $app = Factory::getApplication();
        $input = $app->getInput();
        $qty = $input->getInt('qty');

        if (!empty($qty)) {
            $request .= 'qty/' . $qty . '/';
        }

        $redirect = (string) $input->getString('redirect');

        if ($layout === 'addtocart' && $redirect === '') {
            $redirect = 'checkout/cart';
        }

        if ($redirect !== '') {
            $redirectUrl = UrlHelper::route($redirect);

            if (!empty($redirectUrl)) {
                $request .= 'uenc/' . EncryptionHelper::base64_encode($redirectUrl) . '/';
            }

            $formKey = $bridge->getSessionData('form_key');

            if (!empty($formKey)) {
                $request .= 'form_key/' . $formKey;
            }
        }

        $mode = $params->get('mode');

        if (!empty($mode)) {
            $request .= '?mode=' . $mode;
        }

        $this->setRequest($request);

        if ((int) ConfigModel::load('enable_canonical') === 1) {
            $uri      = UrlHelper::route($request);
            $document = $app->getDocument();
            $document->setMetaData('canonical', $uri);
        }

        $this->setBlock('content');

        parent::display($tpl);
    }
}
