<?php

namespace CJ\DataExport\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class InsertEntity
 */
class InsertBigQuery
    implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var string[]
     */
    protected $entityCode = [
        'bigquery_order',
        'bigquery_sales_order_item'
    ];

    const TABLE_EXPORT = 'eguana_gcrm_data_export_setting';

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritDoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable(self::TABLE_EXPORT),
            ['entity_code'],
            $this->entityCode
        );
        $this->moduleDataSetup->endSetup();
    }
}
