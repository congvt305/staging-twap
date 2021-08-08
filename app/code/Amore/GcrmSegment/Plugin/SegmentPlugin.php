<?php
declare(strict_types=1);
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 07/09/2021
 */

namespace Amore\GcrmSegment\Plugin;

use Magento\CustomerSegment\Model\Segment;
use Magento\Framework\DataObject;

class SegmentPlugin
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $db;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Amore\GcrmDataExport\Model\Config\Config
     */
    private $dataExportConfig;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Amore\GcrmDataExport\Model\Config\Config $dataExportConfig,
        \Magento\Framework\App\ResourceConnection $connection
    ) {
        $this->db = $connection->getConnection();
        $this->logger = $logger;
        $this->dataExportConfig = $dataExportConfig;
    }

    /**
     * @param \Magento\CustomerSegment\Model\Segment $subject
     * @param callable $proceed
     * @param DataObject $customer
     * @param int|string|null $website
     */
    public function aroundValidateCustomer(\Magento\CustomerSegment\Model\Segment $subject, callable $proceed, $customer, $website)
    {
        $isRemote = $subject->getDataByKey('is_remote');
        $remoteCode = $subject->getDataByKey('remote_code');
        if ($isRemote || $remoteCode) {
            return $this->validateRemoteSegmentCustomer($remoteCode, $customer);
        }

        return $proceed($customer, $website);
    }


    /**
     * @param \Magento\CustomerSegment\Model\Segment $subject
     * @param callable $proceed
     * @param DataObject $object
     */
    public function aroundValidate(\Magento\CustomerSegment\Model\Segment $subject, callable $proceed, DataObject $object)
    {
        $isRemote = $subject->getDataByKey('is_remote');
        $remoteCode = $subject->getDataByKey('remote_code');
        if ($isRemote || $remoteCode) {
            return $this->validateRemoteSegmentCustomer($remoteCode, $object);
        }
        return $proceed($object);
    }


    /**
     * @param string $remoteCode
     * @param DataObject $customer
     * @return bool
     */
    private function validateRemoteSegmentCustomer($remoteCode, $customer)
    {
        try {
            if($remoteCode && $customer->getId()) {
                return $this->isValid($remoteCode, $customer->getId());
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }


    /**
     * @param string $remoteCode
     * @param string $customerId
     * @return bool
     */
    private function isValid($remoteCode, $customerId)
    {
        try {
            $host = $this->dataExportConfig->getHerokuHost();
            $dbname = $this->dataExportConfig->getHerokuDBName();
            $user = $this->dataExportConfig->getHerokuUser();
            $password = $this->dataExportConfig->getHerokuPassword();
            $db_connection = pg_connect(
                "host=$host
                         dbname=$dbname
                         user=$user
                         password=$password"
            );
            $query = "select customer_id__c from apgcrm.GECPBannerD__c where isdeleted = 'f' AND segment_id__c = '". $remoteCode . "'";
            $query .= " AND customer_id__c = '" . $customerId . "'";
            $result = pg_query($db_connection, $query);
            $resultRows = pg_fetch_row($result);
            pg_close($db_connection);
            if ($resultRows != false) {
                return true;
            }

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return false;
    }
}
