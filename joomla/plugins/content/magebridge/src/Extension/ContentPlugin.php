<?php

declare(strict_types=1);

namespace MageBridge\Plugin\Content\MageBridge\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use MageBridge\Component\MageBridge\Site\Library\MageBridge;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;

/**
 * MageBridge Content Plugin.
 *
 * @since  3.0.0
 */
class ContentPlugin extends CMSPlugin implements SubscriberInterface, DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    /**
     * Constructor.
     */
    public function __construct(DispatcherInterface $dispatcher, array $config = [])
    {
        parent::__construct($config);
        $this->dispatcher = $dispatcher;
    }

    /**
     * Returns an array of events this subscriber will listen to.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepare' => 'onContentPrepare',
        ];
    }

    /**
     * Event onContentPrepare.
     *
     * @param string $context
     * @param object $row
     * @param \Joomla\Registry\Registry $params
     * @param mixed $page
     *
     * @return bool
     */
    public function onContentPrepare($context, $row, $params, $page)
    {
        // Do not continue if not enabled
        if (!$this->isEnabled()) {
            return false;
        }

        // Check for Magento CMS-tags
        if (!empty($row->text) && preg_match('/{{([^}]+)}}/', $row->text)) {
            // Get system variables
            $bridge = BridgeModel::getInstance();

            // Include the MageBridge register
            // @phpstan-ignore-next-line - Factory::getApplication() returns CMSApplication which has input property
            $option = Factory::getApplication()->input->getCmd('option');
            $key = md5(var_export($row, true)) . ':' . $option;

            // Register this content
            $bridge->register('content', $row->text, $key);

            // Replace the tags
            $row->text = $this->replaceTags($row->text);
        }

        return true;
    }

    /**
     * Replace Magento CMS tags with actual content.
     */
    private function replaceTags(string $text): string
    {
        // Simple implementation - in reality this would parse and replace Magento CMS tags
        return $text;
    }

    /**
     * Check if the plugin is enabled.
     */
    private function isEnabled(): bool
    {
        if (!class_exists(BridgeModel::class)) {
            return false;
        }

        $bridge = BridgeModel::getInstance();

        if ($bridge->isOffline()) {
            return false;
        }

        if (MageBridge::isApiPage()) {
            return false;
        }

        return true;
    }
}
