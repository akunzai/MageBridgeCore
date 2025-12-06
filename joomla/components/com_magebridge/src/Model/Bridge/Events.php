<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Bridge;

defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\Event\DispatcherInterface;

final class Events extends Segment
{
    private $events = null;

    public static function getInstance($name = null)
    {
        return parent::getInstance(self::class);
    }

    public function getResponseData()
    {
        return $this->register->getData('events');
    }

    public function setEvents($data)
    {
        return $this->register->add('event', null, $data);
    }

    public function getEvents()
    {
        if ($this->events === null) {
            $this->events = $this->getResponseData();
        }

        return $this->events;
    }

    public function doEvents(): bool
    {
        $events = $this->getEvents();

        if (empty($events)) {
            return false;
        }

        foreach ($events as $event) {
            $this->dispatchEvent($event);
        }

        return true;
    }

    /**
     * Dispatch an event using modern Joomla 5 event system.
     *
     * @param array $event Event data including 'event' name and 'arguments'
     */
    public function dispatchEvent($event): bool
    {
        if (empty($event['event'])) {
            return false;
        }

        $arguments  = $event['arguments'] ?? [];
        $eventObj   = AbstractEvent::create($event['event'], $arguments);
        $dispatcher = Factory::getContainer()->get(DispatcherInterface::class);
        $dispatcher->dispatch($event['event'], $eventObj);

        return true;
    }
}
