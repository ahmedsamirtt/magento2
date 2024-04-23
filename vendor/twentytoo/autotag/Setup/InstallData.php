<?php
namespace TwentyToo\AutoTag\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;

class InstallData implements InstallDataInterface
{
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

        // Insert data into the twentytoo_tags table
        $data = [
            'order_id' => 123, // Replace with the actual order ID
            'english_tags' => 'English Tags Value', // Replace with the actual English tags value
            'arabic_tags' => 'Arabic Tags Value', // Replace with the actual Arabic tags value
        ];

        $setup->getConnection()->insert($setup->getTable('twentytoo_tags'), $data);

        $setup->endSetup();
    }
}
