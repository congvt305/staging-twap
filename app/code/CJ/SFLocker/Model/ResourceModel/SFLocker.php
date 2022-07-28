<?php

namespace CJ\SFLocker\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Model quote
 */
class SFLocker
{
    const INVENTORY_SOURCE_TABLE = 'inventory_source';
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    )
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function importSFLockers($data, $type)
    {
        foreach ($data as $store) {
            $sfLockers[] = [
                'source_code' => $store[0],
                'name' => $store[4],
                'enabled' => 1,
                'latitude' => $store[6],
                'longitude' => $store[7],
                'country_id' => 'HK',
                'region' => $store[1],
                'city' => $store[2],
                'postcode' => $store[3],
                'street' => $store[5],
                'is_pickup_location_active' => 1,
                'store_type' => $type
            ];
        }
        $conn = $this->getConnection();
        $sourceTable = $conn->getTableName(self::INVENTORY_SOURCE_TABLE);
        $conn->insertOnDuplicate($sourceTable, $sfLockers);
    }

    /**
     * Provides connection
     *
     * @return AdapterInterface
     */
    private function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resourceConnection->getConnection();
        }

        return $this->connection;
    }
}
