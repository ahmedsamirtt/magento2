<?php
namespace TwentyToo\AutoTag\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Product
     */
    private $productModel;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * UpgradeData constructor.
     * @param LoggerInterface $logger
     * @param Product $productModel
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        LoggerInterface $logger,
        Product $productModel,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->logger = $logger;
        $this->productModel = $productModel;
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
            /** @var ProductCollection $productCollection */
            $productCollection = $this->productCollectionFactory->create();

            // Add fields to select
            $productCollection->addAttributeToSelect(['sku', 'name']);

            // Iterate over each product
            foreach ($productCollection as $product) {
                // Load product by ID
                $productId = $product->getId();
                $product = $this->productModel->load($productId);

                // Get product images
                $productImages = $product->getMediaGalleryImages();

                // Initialize an array to store image URLs
                $imageUrls = [];
                foreach ($productImages as $image) {
                    // Get the public URL of the image
                    $imageUrl = $this->getPublicImageUrl($image);
                    if ($imageUrl) {
                        $imageUrls[] = $imageUrl;
                    }
                }

                // Log product ID, image URLs, and other metadata
                $this->logger->info('Product ID: ' . $productId);
                $this->logger->info('Image URLs: ' . json_encode($imageUrls));
                $this->logger->info('Product URL: ' . $product->getProductUrl());
                $this->logger->info('Product Data: ' . json_encode($product->getData()));
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
     * Get the public URL of the image.
     *
     * @param \Magento\Catalog\Model\Product\Image $image
     * @return string|null
     */
    private function getPublicImageUrl($image)
    {
        try {
            // Get the URL of the image
            $imageUrl = $image->getUrl();
            // Check if the URL is accessible
            $headers = get_headers($imageUrl);
            if ($headers && strpos($headers[0], '200')) {
                return $imageUrl;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}
