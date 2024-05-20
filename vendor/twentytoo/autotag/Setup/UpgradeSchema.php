<?php
namespace TwentyToo\AutoTag\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrade schema for the module.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // Check if the table exists
        if (!$setup->tableExists('twentytoo_tags')) {
            // Create the table
            $table = $setup->getConnection()->newTable(
                $setup->getTable('twentytoo_tags')
            )->addColumn(
                'order_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Order ID'
            )->addColumn(
                'english_tags',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'English Tags'
            )->addColumn(
                'arabic_tags',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Arabic Tags'
            )->setComment(
                'TwentyToo Tags Table'
            );

            // Create the table in the database
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
