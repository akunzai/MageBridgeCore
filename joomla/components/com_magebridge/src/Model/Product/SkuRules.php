<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Product;

defined('_JEXEC') or die;

/**
 * Product-connector SKU matching (ALL, exact, comma list, % prefix/suffix).
 */
final class SkuRules
{
    public static function match(string $sku, string $rule): bool
    {
        $sku  = trim($sku);
        $rule = trim($rule);

        if (strtoupper($rule) == 'ALL') {
            return true;
        }

        if ($rule === $sku) {
            return true;
        }

        if (strstr($rule, ',')) {
            foreach (explode(',', $rule) as $subrule) {
                if (self::match($sku, $subrule) === true) {
                    return true;
                }
            }
        }

        if (strstr($rule, '%')) {
            $s = str_replace('%', '', $rule);

            if (preg_match('/^\%/', $rule) && substr($sku, strlen($sku) - strlen($s)) == $s) {
                return true;
            }

            if (preg_match('/\%$/', $rule) && substr($sku, 0, strlen($s)) == $s) {
                return true;
            }
        }

        return false;
    }
}
