<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Users;

defined('_JEXEC') or die;

class HtmlView extends \MageBridge\Component\MageBridge\Administrator\View\BaseHtmlView
{
    public function checkbox($item, $i)
    {
        return '<input type="checkbox" id="cb' . $i . '" name="cid[]" value="' . $item->id . '" onclick="isChecked(this.checked);" />';
    }
}

class_alias('MageBridgeViewUsers', HtmlView::class);
