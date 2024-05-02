<?php
namespace Arsal\CustomTab\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Arsal\CustomTab\Setup
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $table = $installer->getConnection()
            ->newTable($installer->getTable('customtab'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'Entity id'
            )
            ->addColumn(
                'identifier',
                Table::TYPE_TEXT,
                254,
                [
                    'nullable'  => false,
                    'unique'    =>  true
                ],
                'Tab Title'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                254,
                [
                    'nullable' => false,
                    'default' => '',
                ],
                'Tab Title'
            )
            ->addColumn(
                'description',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Tab Description'
            )
            ->addColumn(
                'type',
                Table::TYPE_INTEGER,
                null,
                [],
                'Tab Type'
            )
            ->addColumn(
                'custom_data',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Custom Template Data'
            )
            ->addColumn(
                'sort_order',
                Table::TYPE_INTEGER,
                null,
                [],
                'Sort Order'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => 'CURRENT_TIMESTAMP'
                ],
                'Date Created'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => 'CURRENT_TIMESTAMP'
                ],
                'Date Updated'
            )
            ->setComment('Custom Tabs')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');

        // Create Table.
        $installer->getConnection()->createTable($table);

        $installer->startSetup();
    }
}
