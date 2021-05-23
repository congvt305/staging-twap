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
class AddPosCodeDataForTaiwan implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * AddPosCodeDataForTaiwan constructor.
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
        $this->updatePosCodeData();
        $this->moduleDataSetup->endSetup();
    }

    /**
     * @return void
     */
    private function updatePosCodeData()
    {
        $cityRows = $this->getTwCityRows();
        $adapter = $this->moduleDataSetup->getConnection();
        foreach ($cityRows as $key => $value) {
            $adapter->update(
                $adapter->getTableName('eguana_directory_region_city'),
                [
                    'pos_code' => $value
                ],
                [
                    'city_id=?' => $key
                ]
            );
        }
    }

    /**
     * @return array
     */
    private function getTwRegionIds()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select()
            ->from(['dcr' => $connection->getTableName('directory_country_region')], ['region_id'])
            ->where('country_id=?', 'TW');
        return $connection->fetchCol($select);
    }

    /**
     * @return array
     */
    private function getTwCityRows()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select()
            ->from(['drc' => $connection->getTableName('eguana_directory_region_city')], ['city_id', 'code'])
            ->where('region_id in (?)', $this->getTwRegionIds());
        return $connection->fetchPairs($select);
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
            \Eguana\Directory\Setup\Patch\Data\AddNewDataForTaiwan::class
        ];
    }
}
