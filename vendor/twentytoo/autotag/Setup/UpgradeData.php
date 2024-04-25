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

            // Select all data from catalog_product_entity table
            $select = $connection->select()->from(
                $setup->getTable('catalog_product_entity')
            );

            // Execute the select query
            $data = $connection->fetchAll($select);

            // Log each row of data
            foreach ($data as $row) {
                $this->logger->info('Data from catalog_product_entity: ' . json_encode($row));
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
