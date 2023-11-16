<?php

namespace Amasty\CPS\Setup\InstallSchema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\CPS\Api\Data\BrandProductInterface;

class AddModuleTables
{
    /**
     * Create tables
     *
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $installer)
    {
        $installer->startSetup();

        $mainTable = $installer->getTable(BrandProductInterface::MAIN_TABLE);
        $table = $installer->getConnection()->newTable(
            $mainTable
        )->addColumn(
            BrandProductInterface::AMBRAND_ID,
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Brand Link ID'
        )->addColumn(
            BrandProductInterface::PRODUCT_ID,
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true],
            'Product Link ID'
        )->addColumn(
            BrandProductInterface::STORE_ID,
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Store ID'
        )->addColumn(
            BrandProductInterface::POSITION,
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0],
            'Product Position for Brand'
        )->addColumn(
            BrandProductInterface::IS_PINNED,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => 0],
            'Brand Link ID'
        )->addIndex(
            $installer->getIdxName(
                BrandProductInterface::MAIN_TABLE,
                [
                    BrandProductInterface::AMBRAND_ID,
                    BrandProductInterface::PRODUCT_ID,
                    BrandProductInterface::STORE_ID
                ]
            ),
            [
                BrandProductInterface::AMBRAND_ID,
                BrandProductInterface::PRODUCT_ID,
                BrandProductInterface::STORE_ID
            ],
            ['type' => 'unique']
        )->addIndex(
            $installer->getIdxName(BrandProductInterface::MAIN_TABLE, [BrandProductInterface::AMBRAND_ID]),
            [BrandProductInterface::AMBRAND_ID]
        )->addIndex(
            $installer->getIdxName(BrandProductInterface::MAIN_TABLE, [BrandProductInterface::PRODUCT_ID]),
            [BrandProductInterface::PRODUCT_ID]
        )->addIndex(
            $installer->getIdxName(BrandProductInterface::MAIN_TABLE, [BrandProductInterface::STORE_ID]),
            [BrandProductInterface::STORE_ID]
        )->addForeignKey(
            $installer->getFkName(
                BrandProductInterface::MAIN_TABLE,
                BrandProductInterface::PRODUCT_ID,
                $installer->getTable('catalog_product_entity'),
                'entity_id'
            ),
            BrandProductInterface::PRODUCT_ID,
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Brand Product Table'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getTable('amasty_amshopby_option_setting');
        $connection = $installer->getConnection();

        if ($connection->isTableExists($table)) {
            $connection->addColumn(
                $table,
                'sorting',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Sorting value'
                ]
            );

            $connection->addColumn(
                $table,
                BrandProductInterface::BRAND_USE_DEFAULT_STORE_SETTING,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Use Default Store Sorting'
                ]
            );
        }
    }
}
