<?php
namespace TwentyToo\AutoTag\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Product;

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
     * UpgradeData constructor.
     * @param LoggerInterface $logger
     * @param Product $productModel
     */
    public function __construct(LoggerInterface $logger, Product $productModel)
    {
        $this->logger = $logger;
        $this->productModel = $productModel;
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
            // Get Magento resource connection
            $connection = $setup->getConnection();

            // Select all data from catalog_product_entity table
            $select = $connection->select()->from(
                $setup->getTable('catalog_product_entity')
            );

            // Execute the select query
            $data = $connection->fetchAll($select);

            // Log each row of data along with image URLs and metadata
            foreach ($data as $row) {
                // Load product by ID
                $product = $this->productModel->load($row['entity_id']);
                $productImages = $product->getMediaGalleryImages();

                // Initialize an array to store image URLs
                $imageUrls = [];
                foreach ($productImages as $image) {
                    $imageUrls[] = $image->getUrl();
                }

                // Log product ID, image URLs, and other metadata
                $this->logger->info('Product ID: ' . $row['entity_id']);
                $this->logger->info('Image URLs: ' . json_encode($imageUrls));
                $this->logger->info('Product URL: ' . $product->getProductUrl());
                $this->logger->info('Product Data: ' . json_encode($row));
            }

            // Log a message indicating successful upgrade
            $this->logger->info('Twentytoo upgrade completed successfully.');
        } catch (\Exception $e) {
            // Log any errors that occur during upgrade
            $this->logger->error('Error occurred during module upgrade: ' . $e->getMessage());
        }

        $setup->endSetup();
    }
}
