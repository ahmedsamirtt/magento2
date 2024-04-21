<?php
// File: app/code/TwentyToo/AutoTag/Observer/LogProductData.php
namespace TwentyToo\AutoTag\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;

class LogProductData implements ObserverInterface
{
    protected $logger;
    protected $resourceConnection;

    public function __construct(LoggerInterface $logger, ResourceConnection $resourceConnection)
    {
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(Observer $observer)
    {
        // Check if the event is triggered by Composer installation
        if ($observer->getEvent()->getOperation() == 'install') {
            // Execute your logic to fetch and log data from catalog_product_entity
            $data = $this->getDataFromCatalogProductEntity();

            // Log the data
            $this->logger->info('Data retrieved from catalog_product_entity during Composer installation:', $data);

            // Print the data array itself
            $this->logger->info('Data Array:', $data);

            // Print each product's details
            foreach ($data as $product) {
                $this->logger->info("Product ID: {$product['entity_id']}, SKU: {$product['sku']}, Name: {$product['name']}");
            }
        }
    }

    public function getDataFromCatalogProductEntity()
    {
        // Example function to get data from catalog_product_entity table
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            ['cp' => 'catalog_product_entity'],
            ['entity_id', 'sku', 'name'] // Add columns you need
        );
        $data = $connection->fetchAll($select);
        
        // Process retrieved data as needed

        return $data;
    }
}
