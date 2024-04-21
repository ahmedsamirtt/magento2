<?php
namespace TwentyToo\AutoTag\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\ProductFactory;
use Psr\Log\LoggerInterface;

class UpgradeData implements UpgradeDataInterface
{
    private $logger;
    private $productFactory;

    public function __construct(
        ProductFactory $productFactory,
        LoggerInterface $logger
    ) {
        $this->productFactory = $productFactory;
        $this->logger = $logger;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        try {
            // Fetch data from catalog_product_entity
            $products = $this->productFactory->create()->getCollection();
            foreach ($products as $product) {
                // Log product ID
                $productId = $product->getId();
                $this->logger->info('Processing product with ID: ' . $productId);

                // Process each product and trigger the API endpoint
                $productName = $product->getName();
                $productSku = $product->getSku();
                // Trigger API endpoint with product data
                $this->triggerApiEndpoint($productId, $productName, $productSku);
            }

            // Set flag indicating that the process has been completed
            // You can store this flag in the database or in a configuration file

            $this->logger->info('Setup script executed successfully.');

        } catch (\Exception $e) {
            $this->logger->error('Error during setup script execution: ' . $e->getMessage());
        }

        $setup->endSetup();
    }

    private function triggerApiEndpoint($productId, $productName, $productSku)
    {
        try {
            // Make API call to your endpoint with the product data
            // Example:
            // $httpClient->post('https://your-api-endpoint.com', [
            //     'json' => [
            //         'product_id' => $productId,
            //         'product_name' => $productName,
            //         'product_sku' => $productSku
            //     ]
            // ]);

            // Log success message
            $this->logger->info('API endpoint triggered successfully for product ID: ' . $productId);

        } catch (\Exception $e) {
            // Log error message
            $this->logger->error('Error triggering API endpoint for product ID ' . $productId . ': ' . $e->getMessage());
        }
    }
}