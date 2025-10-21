<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Bridge;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

final class Messages extends Segment
{
    public static function getInstance($name = null)
    {
        return parent::getInstance(self::class);
    }

    public function getResponseData()
    {
        return $this->register->getData('messages');
    }

    public function addMessagesToQueue(): void
    {
        $messages = $this->getResponseData();

        if (empty($messages)) {
            return;
        }

        $app = Factory::getApplication();

        foreach ($messages as $message) {
            if (empty($message['text'])) {
                continue;
            }

            $type = $message['type'] ?? 'message';
            $app->enqueueMessage($message['text'], $type);
        }
    }
}
