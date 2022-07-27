<?php

declare(strict_types=1);

namespace CJ\VLogicTablerate\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateShippingTablerate implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * PatchInitial constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ){
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();
        if ($connection->isTableExists('shipping_vlogic')) {
            $connection->update(
                $this->moduleDataSetup->getTable('shipping_vlogic'),
                ['condition_name' => 'package_value_with_discount'],
                [new \Zend_Db_Expr('condition_name = \'package_value\'')]
            );
            $connection->update(
                $this->moduleDataSetup->getTable('core_config_data'),
                ['value' => 'package_value_with_discount'],
                [
                    new \Zend_Db_Expr('value = \'package_value\''),
                    new \Zend_Db_Expr('path = \'carriers/vlogic/condition_name\'')
                ]
            );
        }
        $this->moduleDataSetup->getConnection()->endSetup();

        $connection->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
