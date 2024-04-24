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
     * InstallData constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Install Data
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        try {
            // Insert data into the twentytoo_tags table
            $data = [
                'order_id' => 123, // Replace with the actual order ID
                'english_tags' => 'English Tags Value', // Replace with the actual English tags value
                'arabic_tags' => 'Arabic Tags Value', // Replace with the actual Arabic tags value
            ];

            $setup->getConnection()->insert($setup->getTable('twentytoo_tags'), $data);

            $this->logger->info('Data inserted successfully during module installation.');
        } catch (\Exception $e) {
            $this->logger->error('Error occurred during data insertion: ' . $e->getMessage());
        }

        $setup->endSetup();
    }
}
