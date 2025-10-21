<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Bridge;

defined('_JEXEC') or die;

use Joomla\CMS\Event\GenericEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Helper\BlockHelper;
use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;
use MageBridge\Component\MageBridge\Site\Helper\MageBridgeHelper;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Register;
use Yireo\Helper\Helper;

final class Block extends Segment
{
    public static function getInstance($name = null)
    {
        return parent::getInstance(self::class);
    }

    public function getResponseData($name, $arguments = null)
    {
        return Register::getInstance()->getData('block', $name, $arguments);
    }

    public function isCachable($name): bool
    {
        $response = $this->getResponse('block', $name);

        return isset($response['meta']['allow_caching'], $response['meta']['cache_lifetime'])
            && (int) $response['meta']['allow_caching'] === 1
            && (int) $response['meta']['cache_lifetime'] > 0;
    }

    public function getBlock($blockName, $arguments = null)
    {
        BridgeModel::getInstance()->build();

        $segment = $this->getResponse('block', $blockName, $arguments);

        if (!isset($segment['data'])) {
            return null;
        }

        $blockData = $segment['data'];

        if (!empty($blockData) && !isset($segment['cache'])) {
            $blockData = $this->decode($blockData);
            $blockData = $this->filterHtml($blockData);
        }

        $blockData = BlockHelper::parseBlock($blockData);

        if ((int) ConfigModel::load('enable_jdoc_tags') === 1) {
            $blockData = BlockHelper::parseJdocTags($blockData);
        }

        if ((int) ConfigModel::load('enable_content_plugins') === 1) {
            $item        = (object) null;
            $item->text  = $blockData;
            $item->params = Helper::toRegistry();

            $plugins = self::getContentPlugins();

            if (!empty($plugins)) {
                foreach ($plugins as $plugin) {
                    PluginHelper::importPlugin('content', $plugin);
                }
            }

            // Dispatch onContentPrepare event using modern Joomla 5 event system
            $event = new GenericEvent('onContentPrepare', [
                'context' => 'com_magebridge.block',
                'item' => &$item,
                'params' => &$item->params,
                'page' => 0,
            ]);
            $dispatcher = Factory::getContainer()->get(DispatcherInterface::class);
            $dispatcher->dispatch('onContentPrepare', $event);
            $blockData = $item->text;
        }

        if ((int) ConfigModel::load('enable_block_rendering') === 1) {
            PluginHelper::importPlugin('magebridge');
            // Dispatch onBeforeDisplayBlock event using modern Joomla 5 event system
            $event = new GenericEvent('onBeforeDisplayBlock', [
                'blockName' => &$blockName,
                'arguments' => $arguments,
                'blockData' => &$blockData,
            ]);
            $dispatcher = Factory::getContainer()->get(DispatcherInterface::class);
            $dispatcher->dispatch('onBeforeDisplayBlock', $event);
        }

        return $blockData;
    }

    public function decode($blockData)
    {
        return EncryptionHelper::base64_decode($blockData);
    }

    public function filterHtml($html)
    {
        $html = MageBridgeHelper::filterContent($html);
        $replacementUrls = UrlHelper::getReplacementUrls();

        if (!empty($replacementUrls)) {
            foreach ($replacementUrls as $replacementUrl) {
                $source      = $replacementUrl->source;
                $destination = $replacementUrl->destination;

                if ($replacementUrl->source_type == 0) {
                    $source = UrlHelper::route($source);
                } else {
                    $source = str_replace('/', '\/', $source);
                }

                if (preg_match('/^index\.php\?option=/', $destination)) {
                    $destination = Route::_($destination);
                }

                if ($replacementUrl->source_type == 0) {
                    $html = str_replace($source . "'", $destination . "'", $html);
                    $html = str_replace($source . '"', $destination . '"', $html);
                } else {
                    $html = preg_replace('/href="([^\"]+)' . $source . '([^\"]+)/', 'href="' . $destination, $html);
                }
            }
        }

        return $html;
    }

    public static function getContentPlugins(): array
    {
        static $plugins = null;

        if ($plugins !== null) {
            return $plugins;
        }

        $plugins = [];
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $query   = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('content'))
            ->where($db->quoteName('enabled') . ' = 1');

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        if (!empty($rows)) {
            foreach ($rows as $row) {
                if (!preg_match('/magebridge/i', $row->element)) {
                    $plugins[] = $row->element;
                }
            }
        }

        return $plugins;
    }
}
