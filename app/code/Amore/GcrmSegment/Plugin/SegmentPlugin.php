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

    public function __construct(\Magento\Framework\App\ResourceConnection $connection)
    {
        $this->db = $connection->getConnection();
    }

    /**
     * @param \Magento\CustomerSegment\Model\Segment $subject
     * @param callable $proceed
     * @param DataObject $customer
     * @param Website|string|null $website
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
            if($remoteCode) {
                if ($this->getCustomerIntegrationNumber($customer)) {
                    return $this->isValid($remoteCode, $this->getCustomerIntegrationNumber($customer));
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }


    /**
     * @param string $remoteCode
     * @param string $customerIntegrationNumber
     * @return bool
     */
    private function isValid($remoteCode, $customerIntegrationNumber)
    {
        $select = $this->db->select()
            ->from('amore_gcrm_bannerd')
            ->where('cstmintgseq = ?', $customerIntegrationNumber)
            ->where('segcd = ?', $remoteCode);

        $raws = $this->db->fetchAll($select);

        return count($raws) > 0;
    }

    /**
     * @param DataObject $customer
     * @return string
     */
    private function getCustomerIntegrationNumber($customer)
    {
        if ($customer->getCustomAttribute('integration_number')) {
            $customerIntegrationNumber = $customer->getCustomAttribute('integration_number')->getValue();
        } elseif ($customer->getDataByKey('integration_number')) {
            $customerIntegrationNumber = $customer->getDataByKey('integration_number');
        } else {
            $customerIntegrationNumber = '';
        }
        return $customerIntegrationNumber;
    }
}
