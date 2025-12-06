<?php

declare(strict_types=1);

namespace MageBridge\Module\MageBridgeProgress\Site\Helper;

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;

/**
 * Helper class for the MageBridge Progress module.
 *
 * @since  3.0.0
 */
class ProgressHelper
{
    /**
     * Method to be called once the MageBridge is loaded.
     *
     * @return array<array-key, array{0: string, 1?: string}>
     */
    public static function register(?Registry $params = null): array
    {
        $register = [];
        $register[] = ['block', 'checkout.progress'];

        $loadCss = (int) ($params?->get('load_css', 1) ?? 1);
        $loadJs  = (int) ($params?->get('load_js', 1) ?? 1);

        if ($loadCss === 1 || $loadJs === 1) {
            $register[] = ['headers'];
        }

        return $register;
    }

    /**
     * Fetch the content from the bridge.
     */
    public static function build(?Registry $params = null): ?string
    {
        $bridge = BridgeModel::getInstance();

        if ((int) ($params?->get('load_css', 1) ?? 1) === 1) {
            $bridge->setHeaders('css');
        }

        if ((int) ($params?->get('load_js', 1) ?? 1) === 1) {
            $bridge->setHeaders('js');
        }

        return $bridge->getBlock('checkout.progress');
    }
}
