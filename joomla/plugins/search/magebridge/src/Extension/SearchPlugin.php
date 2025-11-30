<?php

declare(strict_types=1);

namespace MageBridge\Plugin\Search\MageBridge\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\Register;

/**
 * MageBridge Search Plugin.
 *
 * @since 4.0.0
 */
class SearchPlugin extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentSearchAreas' => 'onContentSearchAreas',
            'onContentSearch' => 'onContentSearch',
        ];
    }

    /**
     * Constructor.
     *
     * @param array<string, mixed> $config Plugin configuration
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->loadLanguage();
    }

    /**
     * Handle the event when searching for items - return search areas.
     *
     * @return array<string, string>
     */
    public function onContentSearchAreas(): array
    {
        if ($this->isEnabled() === false) {
            return [];
        }

        return [
            'mage-products' => 'PLG_SEARCH_MAGEBRIDGE_PRODUCTS',
            'mage-categories' => 'PLG_SEARCH_MAGEBRIDGE_CATEGORIES',
        ];
    }

    /**
     * Handle the event when searching for items.
     *
     * @param string $text Search text
     * @param string $phrase Search phrase type
     * @param string $ordering Result ordering
     * @param array<int, string>|null $areas Search areas
     *
     * @return array<int, object>
     */
    public function onContentSearch(
        string $text,
        string $phrase = '',
        string $ordering = '',
        ?array $areas = null
    ): array {
        if ($this->isEnabled() === false) {
            return [];
        }

        // Check if the areas match
        if (!empty($areas)) {
            if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
                return [];
            }
        }

        // Do not continue with an empty search string
        if (empty($text)) {
            return [];
        }

        // Load the plugin parameters
        $searchLimit = (int) $this->params->get('search_limit', 50);
        $searchFieldsParam = trim((string) $this->params->get('search_fields', ''));

        // Determine the search fields
        $searchFields = $this->parseSearchFields($searchFieldsParam);

        // Build the search array
        $searchOptions = [
            'store' => $this->getStoreCode(),
            'website' => ConfigModel::load('website'),
            'text' => $text,
            'search_limit' => $searchLimit,
            'search_fields' => $searchFields,
        ];

        // Include the MageBridge register
        DebugModel::getInstance()->trace('Search plugin');
        $register = Register::getInstance();
        $segmentId = $register->add('api', 'magebridge_product.search', $searchOptions);

        // Include the MageBridge bridge
        $bridge = BridgeModel::getInstance();
        $bridge->build();

        // Get the results
        $results = $register->getDataById($segmentId);

        // Do not continue if the result is empty
        if (empty($results) || !is_array($results)) {
            return [];
        }

        // Only show the maximum amount
        $results = array_slice($results, 0, $searchLimit);

        return $this->formatResults($results);
    }

    /**
     * Parse search fields from parameter string.
     *
     * @return array<int, string>
     */
    private function parseSearchFields(string $searchFieldsParam): array
    {
        if (empty($searchFieldsParam)) {
            return ['title', 'description'];
        }

        $searchFieldValues = explode(',', $searchFieldsParam);
        $searchFields = [];

        foreach ($searchFieldValues as $searchFieldValue) {
            $trimmed = trim($searchFieldValue);
            if ($trimmed !== '') {
                $searchFields[] = $trimmed;
            }
        }

        return array_unique($searchFields);
    }

    /**
     * Format search results into standard objects.
     *
     * @param array<int, array<string, mixed>> $results
     *
     * @return array<int, object>
     */
    private function formatResults(array $results): array
    {
        $objects = [];

        foreach ($results as $result) {
            if (!is_array($result)) {
                continue;
            }

            $object = new \stdClass();
            $object->title = $result['name'] ?? '';
            $object->text = $result['description'] ?? '';
            $url = $result['url'] ?? '';
            $object->href = preg_replace('/^(.*)index.php/', 'index.php', $url);
            $object->created = $result['created_at'] ?? '';
            $object->metadesc = $result['meta_description'] ?? '';
            $object->metakey = $result['meta_keyword'] ?? '';
            $object->section = null;
            $object->browsernav = 2;
            $object->thumbnail = $result['thumbnail'] ?? '';
            $object->small_image = $result['small_image'] ?? '';
            $object->image = $result['image'] ?? '';

            $objects[] = $object;
        }

        return $objects;
    }

    /**
     * Get the current store code.
     */
    private function getStoreCode(): ?string
    {
        // Try to get store from connector if available
        if (class_exists('MageBridgeConnectorStore')) {
            /** @var object|null $connector */
            $connector = \MageBridgeConnectorStore::getInstance();
            if ($connector !== null && method_exists($connector, 'getStore')) {
                return $connector->getStore();
            }
        }

        return null;
    }

    /**
     * Return whether MageBridge is available or not.
     */
    private function isEnabled(): bool
    {
        if (!class_exists(BridgeModel::class)) {
            return false;
        }

        return BridgeModel::getInstance()->isOffline() === false;
    }
}
