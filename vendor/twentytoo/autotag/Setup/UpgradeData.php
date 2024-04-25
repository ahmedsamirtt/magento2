<?php
namespace TwentyToo\AutoTag\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

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
     * UpgradeData constructor.
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
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
            $productCollection->addAttributeToSelect(['sku', 'name', 'image']);

            // Iterate over each product
            foreach ($productCollection as $product) {
                // Get product ID
                $productId = $product->getId();

                // Get product image URL
                $productImageUrl = $this->getProductImageUrl($product);

                // Log product ID, image URL, and product URL
                $this->logger->info('Product ID: ' . $productId);
                $this->logger->info('Product Image URL: ' . $productImageUrl);
                $this->logger->info('Product URL: ' . $product->getProductUrl());
            }

            // Log a message indicating successful upgrade
            $this->logger->info('TwentyToo upgrade completed successfully.');
        } catch (\Exception $e) {
            // Log any errors that occur during upgrade
            $this->logger->error('Error occurred during module upgrade: ' . $e->getMessage());
        }

        $setup->endSetup();
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
}
