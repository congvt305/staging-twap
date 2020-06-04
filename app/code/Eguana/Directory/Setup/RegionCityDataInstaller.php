<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 5/30/20
 * Time: 8:20 AM
 */

namespace Eguana\Directory\Setup;


use Magento\Directory\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class RegionCityDataInstaller
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var Data
     */
    private $data;

    /**
     * RegionCityDataInstaller constructor.
     * @param ResourceConnection $resourceConnection
     * @param Data $data
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Data $data
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->data = $data;
    }

    /**
     * @param AdapterInterface $adapter
     * @param array $data
     */
    public function addCountryRegionsCities(AdapterInterface $adapter, array $data)
    {
        foreach ($data as $row) {
            $bind = ['country_id' => $row[0], 'code' => $row[1], 'default_name' => $row[2]];
            $adapter->insert($this->resourceConnection->getTableName('directory_country_region'), $bind);
            $regionId = $adapter->lastInsertId($this->resourceConnection->getTableName('directory_country_region'));
            $bind = ['locale' => 'en_US', 'region_id' => $regionId, 'name' => $row[2]];
            $adapter->insert($this->resourceConnection->getTableName('directory_country_region_name'), $bind);
            $cityData = $row[3];
            foreach ($cityData as $cityRow) {
                $bind = ['region_id' => $regionId, 'code' => $cityRow[0], 'default_name' => $cityRow[1]];
                $adapter->insert($this->resourceConnection->getTableName('eguana_directory_region_city'), $bind);
            }
        }

        /**
         * Upgrade core_config_data general/region/state_required field.
         */
        $countries = $this->data->getCountryCollection()->getCountriesWithRequiredStates();
        $adapter->update(
            $this->resourceConnection->getTableName('core_config_data'),
            [
                'value' => implode(',', array_keys($countries))
            ],
            [
                'scope="default"',
                'scope_id=0',
                'path=?' => \Magento\Directory\Helper\Data::XML_PATH_STATES_REQUIRED
            ]
        );
    }
}

