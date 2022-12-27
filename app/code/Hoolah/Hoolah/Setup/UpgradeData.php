<?php
    namespace Hoolah\Hoolah\Setup;
    
    class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
    {
        private $quoteSetupFactory;
        private $salesSetupFactory;
        
        public function __construct(
            \Magento\Quote\Setup\QuoteSetupFactory $quoteSetupFactory,
            \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory
        )
        {
            $this->quoteSetupFactory = $quoteSetupFactory;
            $this->salesSetupFactory = $salesSetupFactory;
        }
        
        public function upgrade(
            \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
            \Magento\Framework\Setup\ModuleContextInterface $context
        )
        {
            $setup->startSetup();
            
            // we do the schema updating here because of the next lines - they need ModuleDataSetupInterface
            $quoteSetup  = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);
            $salesSetup  = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);
            
            if (!$context->getVersion())
            {
                $salesSetup ->addAttribute(
                    \Magento\Sales\Model\Order::ENTITY,
                    'hoolah_order_context_token',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length'=> 36,
                        'nullable' => true,
                        'required' => false,
                        'visible' => false,
                        'visible_on_front' => false
                    ]
                );
                $salesSetup ->addAttribute(
                    \Magento\Sales\Model\Order::ENTITY,
                    'hoolah_order_ref',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length'=> 36,
                        'nullable' => true,
                        'required' => false,
                        'visible' => false,
                        'visible_on_front' => false
                    ]
                );
            }

            if (version_compare($context->getVersion(), '2.3.21', '<'))
                $quoteSetup ->addAttribute(
                    'quote',
                    'hoolah_order_context_token',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length'=> 36,
                        'nullable' => true,
                        'required' => false,
                        'visible' => false,
                        'visible_on_front' => false
                    ]
                );
            
            if (version_compare($context->getVersion(), '2.3.47', '<'))
                $quoteSetup ->addAttribute(
                    'quote',
                    'hoolah_order_ref',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length'=> 36,
                        'nullable' => true,
                        'required' => false,
                        'visible' => false,
                        'visible_on_front' => false
                    ]
                );
            
            if (version_compare($context->getVersion(), '2.3.61', '<'))
            {
                $quoteSetup ->addAttribute(
                    'quote',
                    'hoolah_update_started_at',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        'length'=> null,
                        'nullable' => true,
                        'required' => false,
                        'visible' => false,
                        'visible_on_front' => false
                    ]
                );

                $quoteSetup ->addAttribute(
                    'quote',
                    'hoolah_update_finished_at',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        'length'=> null,
                        'nullable' => true,
                        'required' => false,
                        'visible' => false,
                        'visible_on_front' => false
                    ]
                );

                $quoteSetup ->addAttribute(
                    'quote',
                    'hoolah_update_attempts',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'length'=> 5,
                        'unsigned' => true,
                        'nullable' => true,
                        'required' => false,
                        'visible' => false,
                        'visible_on_front' => false
                    ]
                );
            }

            $setup->endSetup();
        }
    }