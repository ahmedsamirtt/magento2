<?php
namespace TwentyToo\AutoTag\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Psr\Log\LoggerInterface;

class InstallData implements InstallDataInterface
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
     * @inheritdoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            // Your installation logic here
            $this->logger->info('TwentyToo_AutoTag module installed successfully.');
            
            // Example of an error to log
            throw new \Exception('An error occurred during module installation.');
            
        } catch (\Exception $e) {
            // Log the error
            $this->logger->error('Error during module installation: ' . $e->getMessage());
        }
    }
}
