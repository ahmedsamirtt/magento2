<?php
// File: app/code/Vendor/Module/Setup/InstallData.php

namespace TwentyToo\AutoTag\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
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
     * Install data method
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        // Your logic to trigger the logging goes here
        $this->triggerLog();
    }

    /**
     * Method to log your message
     */
    private function triggerLog()
    {
        // Your message to be logged
        $message = 'Triggered on module installation';

        // Log the message
        $this->logger->info($message);
    }
}
