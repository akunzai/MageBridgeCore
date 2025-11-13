<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Product;

defined('_JEXEC') or die;

/**
 * @property mixed $item
 * @property mixed $actions_form
 * @property mixed $params_form
 */
class HtmlView extends \MageBridge\Component\MageBridge\Administrator\View\BaseHtmlView
{
}

class_alias('MageBridgeViewProduct', HtmlView::class);
