<?php

namespace TwentyToo\AutoTag\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Psr\Log\LoggerInterface;

class InstallData implements ModuleDataSetupInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * InstallData constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Installs data for the module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->triggerLog();
    }

    /**
     * Method to log a message about module installation
     *
     * @return void
     */
    private function triggerLog()
    {
        $message = 'TwentyToo AutoTag module installed.';
        $this->logger->info($message);
    }
}