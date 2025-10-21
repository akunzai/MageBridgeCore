<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Element;

use Joomla\CMS\Pagination\Pagination;
use MageBridge\Component\MageBridge\Administrator\View\BaseHtmlView;

defined('_JEXEC') or die;

/**
 * @property array<string, mixed> $lists
 * @property Pagination $pagination
 * @property array<int, array<string, mixed>> $categories
 * @property array<int, array<string, mixed>> $customers
 * @property array<int, array<string, mixed>> $products
 * @property array<int, array<string, mixed>> $widgets
 * @property string|null $object
 * @property string|null $current
 */
class HtmlView extends BaseHtmlView
{
}

class_alias(HtmlView::class, 'MageBridgeViewElement');
