<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 6/7/21
 * Time: 3:59 PM
 */
namespace Amore\GcrmDataExport\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class to insert data into newly made table
 * By Data Patch
 * Class InsertEntity
 */
class InsertEntity implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * InsertEntity constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Add new records in table
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $entityCodeData = [
            ['entity_code' => 'order'],
            ['entity_code' => 'sales_order_item'],
            ['entity_code' => 'quote'],
            ['entity_code' => 'quote_item']
        ];
        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('amore_custom_schedule_date'),
            ['entity_code'],
            $entityCodeData
        );
        $this->moduleDataSetup->endSetup();
    }

    /**
     * Abstract Function for getting Dependencies
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Abstract Function for getting Aliases
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
