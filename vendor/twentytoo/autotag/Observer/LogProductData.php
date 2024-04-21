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
        $event = $observer->getEvent()->getName();

        switch ($event) {
            case 'composer_packages_install_after':
                $this->handleComposerInstall();
                break;
            case 'cache_flush_system':
                $this->handleCacheFlush();
                break;
            case 'magento_migrations_data_migrated':
                $this->handleDataMigration();
                break;
        }
    }

    protected function handleComposerInstall()
    {
        // Logic to handle Composer installation event
        $this->logger->info('Handling Composer installation event.');
    }

    protected function handleCacheFlush()
    {
        // Logic to handle cache flush event
        $this->logger->info('Handling cache flush event.');
    }

    protected function handleDataMigration()
    {
        // Logic to handle data migration event
        $this->logger->info('Handling data migration event.');

        // Example: Fetch data from catalog_product_entity table
        $data = $this->getDataFromCatalogProductEntity();

        // Log fetched data
        $this->logger->info('Data retrieved from catalog_product_entity during data migration:', $data);
    }

    protected function getDataFromCatalogProductEntity()
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