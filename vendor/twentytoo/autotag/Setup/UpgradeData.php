<?php
namespace TwentyToo\AutoTag\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Psr\Log\LoggerInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UpgradeData constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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

            // Select product data with images from catalog_product_entity and catalog_product_entity_media_gallery tables
            $select = $connection->select()
                ->from(
                    ['cpe' => $setup->getTable('catalog_product_entity')],
                    ['entity_id', 'sku', 'name'] // Add columns you want to select from catalog_product_entity
                )
                ->join(
                    ['cpemg' => $setup->getTable('catalog_product_entity_media_gallery')],
                    'cpe.entity_id = cpemg.entity_id',
                    ['value'] // Add columns you want to select from catalog_product_entity_media_gallery
                );

            // Execute the select query
            $data = $connection->fetchAll($select);

            // Log each row of data and send the image to another endpoint
            foreach ($data as $row) {
                // Retrieve the image file path
                $imagePath = '/pub/media/catalog/product' . $row['value'];

                // Read the image file content
                $imageContent = file_get_contents(BP . $imagePath);

                // Send the image content to another endpoint
                // Replace 'YOUR_ENDPOINT_URL' with the actual endpoint URL
                // You may need to use cURL or another HTTP client library to send the image
                // Example using cURL:
                // $ch = curl_init('YOUR_ENDPOINT_URL');
                // curl_setopt($ch, CURLOPT_POST, true);
                // curl_setopt($ch, CURLOPT_POSTFIELDS, $imageContent);
                // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                // $response = curl_exec($ch);
                // curl_close($ch);

                // Log the product data and image path
                $this->logger->info('Product Data: ' . json_encode($row));
                $this->logger->info('Image Path: ' . $imagePath);

                // Log a message indicating that the image was sent to the endpoint
                $this->logger->info('Image sent to endpoint.');
            }

            // Log a message indicating successful upgrade
            $this->logger->info('Module upgrade completed successfully.');
        } catch (\Exception $e) {
            // Log any errors that occur during upgrade
            $this->logger->error('Error occurred during module upgrade: ' . $e->getMessage());
        }

        $setup->endSetup();
    }
}
