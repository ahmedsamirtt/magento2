<?php
namespace TwentyToo\AutoTag\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Zend\Http\Client;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * UpgradeData constructor.
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param ProductCollectionFactory $productCollectionFactory
     * @param Client $httpClient
     */
    public function __construct(
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ProductCollectionFactory $productCollectionFactory,
        Client $httpClient
    ) {
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->httpClient = $httpClient;
    }

    /**
     * Upgrade data for the module.
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        try {
            // Retrieve product collection
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToSelect(['sku', 'name', 'image', 'meta_title', 'meta_description']);

            // Array to hold product data
            $productDataArray = [];

            // Iterate over each product
            foreach ($productCollection as $product) {
                // Get product data
                $productData = $this->prepareProductData($product);
                $productDataArray[] = $productData;
            }

            // Log a message of how payload look
            $this->logger->info('TwentyToo Payload: ' . json_encode($productDataArray));

            // Call API with product data array
            $this->callApi($productDataArray);

            // Log a message indicating successful upgrade
            $this->logger->info('TwentyToo upgrade completed successfully.');
        } catch (\Exception $e) {
            // Log any errors that occur during upgrade
            $this->logger->error('Error occurred during module upgrade: ' . $e->getMessage());
        }

        $setup->endSetup();
    }

    /**
     * Prepare product data in the required format for API call.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    private function prepareProductData($product)
    {
        $productData = [
            'title' => $product->getName(),
            'description' => $product->getMetaDescription(),
            //'img' => $this->getProductImageUrl($product),
            //'department' => $product->getAttributeText('product_type'),
            'id' => $product->getId(),
            'target_audience' => $product->getTags()
        ];

        return $productData;
    }

    /**
     * Get the product image URL.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string|null
     */
    private function getProductImageUrl($product)
    {
        try {
            $mediaBaseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $productImageUrl = $mediaBaseUrl . 'catalog/product' . $product->getImage();
            return $productImageUrl;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Call the API with product data array.
     *
     * @param array $productDataArray
     * @return void
     */
   private function callApi($productDataArray)
{
    $apiUrl = 'https://apidev.twentytoo.ai/cms/v1/data-load';
    $headers = new \Laminas\Http\Headers();
    $headers->addHeaders([
        'Content-Type' => 'application/json',
        'language' => 'en',
        'x-api-key' => 'nm5jubrhx8',
        'uploadType' => 'webhook'
    ]);

    $request = new \Zend\Http\Request();
    $request->setUri($apiUrl);
    $request->setMethod(\Zend\Http\Request::METHOD_POST);
    $request->setHeaders($headers); // Set headers here
    $request->setContent(json_encode($productDataArray));

    $response = $this->httpClient->send($request);

    if ($response->isSuccess()) {
        $this->logger->info('API call successful.');
    } else {
        $this->logger->error('API call failed: ' . $response->getBody());
    }
}
}
