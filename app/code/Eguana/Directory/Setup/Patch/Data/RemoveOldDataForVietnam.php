<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Eguana\Directory\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
* Patch is mechanism, that allows to do atomic upgrade data changes
*/
class RemoveOldDataForVietnam implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $this->removeOldRegionCityData();
        $this->removeOldRegionNameData();
        $this->moduleDataSetup->endSetup();
    }

    private function removeOldRegionCityData()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select()
            ->from(['e' => $connection->getTableName('eguana_directory_region_city')])
            ->where('region_id not in (?)', $this->getValidRegionIds());
        $sql = $select->deleteFromSelect('e');
        $connection->query($sql);
    }

    /**
     * @return array
     */
    private function getValidRegionIds()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select()
            ->from(['dcr' => $connection->getTableName('directory_country_region')], ['region_id']);
        return $connection->fetchCol($select);
    }

    private function removeOldRegionNameData()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select()
            ->from(['d' => $connection->getTableName('directory_country_region_name')])
            ->where('region_id not in (?)', $this->getValidRegionIds());
        $sql = $select->deleteFromSelect('d');
        $connection->query($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            \Eguana\Directory\Setup\Patch\Data\AddNewDataForVietnam::class
        ];
    }
}
