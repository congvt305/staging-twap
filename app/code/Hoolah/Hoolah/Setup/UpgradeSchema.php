<?php
    namespace Hoolah\Hoolah\Setup;
    
    class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
    {
        public function upgrade(
            \Magento\Framework\Setup\SchemaSetupInterface $setup,
            \Magento\Framework\Setup\ModuleContextInterface $context
        ) {
            $setup->startSetup();
            
            if (!$setup->tableExists('hoolah_log'))
            {
                $table = $setup->getConnection()->newTable(
                    $setup->getTable('hoolah_log')
                )
                    ->addColumn(
                        'ip',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        45,
                        ['nullable => false'],
                        'Server IP address'
                    )
                    ->addColumn(
                        'thread',
                        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        null,
                        [ 'nullable' => false, 'unsigned' => true],
                        'Some execution thread ID'
                    )
                    ->addColumn(
                        'sequence',
                        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        null,
                        [ 'nullable' => false, 'unsigned' => true],
                        'A sequence of entries within a thread'
                    )
                    ->addColumn(
                        'description',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        '64k',
                        [ 'nullable' => false],
                        'Human readable description'
                    )
                    ->addColumn(
                        'details',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        '2m',
                        [],
                        'All the details about the incident'
                    )
                    ->addColumn(
                        'created_at',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                        'Created at'
                    )
                    ->addIndex(
                        $setup->getIdxName('hoolah_log', ['created_at']),
                        ['created_at']
                    )
                    ->setComment('hoolah logs table');
                $setup->getConnection()->createTable($table);
            }
            
            $setup->endSetup();
        }
    }