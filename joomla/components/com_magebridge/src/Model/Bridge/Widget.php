<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Bridge;

defined('_JEXEC') or die;

final class Widget extends Segment
{
    public static function getInstance($name = null)
    {
        return parent::getInstance(self::class);
    }

    public function getResponseData($name = null, $arguments = null, $id = null)
    {
        return $this->register->getData('widget', $name, $arguments, $id);
    }
}
