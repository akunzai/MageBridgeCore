<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Bridge;

defined('_JEXEC') or die;

use MageBridge\Component\MageBridge\Site\Model\Register;

final class Api extends Segment
{
    public static function getInstance($name = null)
    {
        return parent::getInstance(self::class);
    }

    public function getResponseData($resource = null, $arguments = null, $id = null)
    {
        return Register::getInstance()->getData('api', $resource, $arguments, $id);
    }
}
