<?php

declare(strict_types=1);

namespace MageBridge\Plugin\Finder\MageBridge;

defined('_JEXEC') or die;

use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\QueryInterface;
use MageBridge\Component\MageBridge\Site\Library\MageBridge;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Register;

/**
 * MageBridge Finder Plugin.
 *
 * @since  3.0.0
 */
class FinderPlugin extends Adapter
{
    /**
     * @var string
     */
    protected $context = 'MageBridge';

    /**
     * @var string
     */
    protected $extension = 'com_magebridge';

    /**
     * @var string
     */
    protected $layout = 'magebridge';

    /**
     * @var string
     */
    protected $type_title = 'Product';

    /**
     * Constructor.
     *
     * @param array<string, mixed> $config Configuration array
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->loadLanguage();
    }

    /**
     * Method to setup this finder-plugin.
     *
     * @return bool
     */
    protected function setup()
    {
        return true;
    }

    /**
     * Method to index a single item.
     *
     * @return bool true on success
     */
    protected function index(Result $item)
    {
        // Add the type taxonomy data.
        $item->addTaxonomy('Type', 'Product');

        // @todo: Add the category taxonomy data.
        //$item->addTaxonomy('Category', $item->category);

        // @todo: Add the language taxonomy data.
        //$item->addTaxonomy('Language', $item->language);

        // Index the item.
        $this->indexer->index($item);

        return true;
    }

    /**
     * Method to load all products through the API.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return array<int, array<string, mixed>>
     */
    protected function loadProducts($offset, $limit)
    {
        // Get the main variables
        $bridge = MageBridge::getBridge();
        assert($bridge instanceof BridgeModel);
        $register = Register::getInstance();

        // Calculate the Magento page
        $page = round($offset / $limit);

        // Setup the arguments and register this request
        $arguments = ['search' => 1, 'page' => $page, 'count' => $limit, 'visibility' => [3, 4]];
        $id = $register->add('api', 'magebridge_product.list', $arguments);

        // Build the bridge
        $bridge->build();

        // Get the requested data from the register
        $data = $register->getDataById($id);

        if (!is_array($data)) {
            return [];
        }

        /** @var array<int, array<string, mixed>> */
        return $data;
    }

    /**
     * Method to index all items.
     *
     * @param int $offset
     * @param int $limit
     * @param QueryInterface|null $query
     *
     * @return array<int, Result>
     */
    protected function getItems($offset, $limit, $query = null)
    {
        $items = [];
        $products = $this->loadProducts($offset, $limit);

        // Loop through the products to build the item-array
        foreach ($products as $product) {
            if (!is_array($product)) {
                continue;
            }

            //$this->debug("page [$offset;$limit] ".$product['name']);

            // Construct a basic class
            /** @var Result&object{id?:int,request?:string,summary?:string,image?:string,small_image?:string,layout?:string} $item */
            $item = new Result();

            // Add basics
            // @phpstan-ignore property.notFound (Result uses dynamic properties)
            $item->id = (int) ($product['product_id'] ?? 0);
            $item->title = (string) ($product['name'] ?? '');

            // Add URLs
            $requestPath = (string) ($product['url_path'] ?? '');
            // @phpstan-ignore property.notFound (Result uses dynamic properties)
            $item->request = $requestPath;
            $item->url = 'index.php?option=com_magebridge&view=root&request=' . $requestPath;
            $item->route = 'index.php?option=com_magebridge&view=root&request=' . $requestPath;

            // Add body-text
            if (!empty($product['short_description'])) {
                // @phpstan-ignore property.notFound (Result uses dynamic properties, mixed from array)
                $item->summary = (string) $product['short_description'];
            } elseif (!empty($product['description'])) {
                // @phpstan-ignore property.notFound (Result uses dynamic properties, mixed from array)
                $item->summary = (string) $product['description'];
            } else {
                // @phpstan-ignore property.notFound (Result uses dynamic properties)
                $item->summary = '';
            }

            // Add additional data
            // @phpstan-ignore property.notFound (Result uses dynamic properties, mixed from array)
            $item->image = (string) ($product['image'] ?? '');
            // @phpstan-ignore property.notFound (Result uses dynamic properties, mixed from array)
            $item->small_image = (string) ($product['small_image'] ?? '');
            // @phpstan-ignore property.notFound (Result uses dynamic properties)
            $item->layout = $this->layout;
            $item->type_id = $this->getTypeId();

            // Add some flags
            $item->published = 1;
            $item->state = 1;
            $item->access = 1;
            $item->language = 'en-GB'; // @todo

            // Add pricing
            // @todo: Why is in the finder-database but not documented?
            $item->list_price = $product['price_raw'] ?? 0;
            $item->sale_price = $product['price_raw'] ?? 0;

            // Add extra search terms
            if (isset($product['search']) && is_array($product['search'])) {
                foreach ($product['search'] as $searchName => $searchValue) {
                    $item->$searchName = $searchValue;
                    $item->addInstruction((string) Indexer::TEXT_CONTEXT, (string) $searchName);
                }
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Method to get the total of products.
     *
     * @return int
     */
    protected function getContentCount()
    {
        // Get the main variables
        $bridge = MageBridge::getBridge();
        assert($bridge instanceof BridgeModel);
        $register = Register::getInstance();

        // Register this API-request
        $arguments = [];
        $id = $register->add('api', 'magebridge_product.count', $arguments);

        // Build the bridge
        $bridge->build();

        // Return the product-count
        $count = $register->getDataById($id);

        if (!is_int($count)) {
            return 0;
        }

        return $count;
    }

    /**
     * Helper method for debugging.
     *
     * @param string $msg
     * @param mixed $var
     */
    protected function debug($msg, $var = null)
    {
        if ($var != null) {
            $msg .= ': ' . var_export($var, true);
        }

        $msg = $msg . "\n";
        //file_put_contents('/tmp/magebridge_finder.log', $msg, FILE_APPEND);
    }
}
