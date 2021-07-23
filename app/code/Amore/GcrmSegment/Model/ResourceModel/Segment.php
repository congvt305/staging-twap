<?php
declare(strict_types=1);
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 07/16/2021
 */

namespace Amore\GcrmSegment\Model\ResourceModel;

class Segment extends \Magento\CustomerSegment\Model\ResourceModel\Segment
{
    /**
     * Process conditions.
     *
     * @param \Magento\CustomerSegment\Model\Segment $segment
     * @return \Magento\CustomerSegment\Model\ResourceModel\Segment
     * @throws \Exception
     */
    protected function processConditions($segment)
    {
        $websiteIds = $segment->getWebsiteIds();
        $relatedCustomers = [];
        if (!empty($websiteIds)) {
            $relatedCustomers = $this->getRelatedCustomers($segment, $websiteIds);
        }
        $this->saveSegmentCustomers($relatedCustomers, $segment);
        return $this;
    }
    /**
     * Save customers segment
     *
     * @param iterable $relatedCustomers
     * @param \Magento\CustomerSegment\Model\Segment $segment
     * @return \Magento\CustomerSegment\Model\ResourceModel\Segment
     * @throws \Exception
     */
    private function saveSegmentCustomers(
        iterable $relatedCustomers,
        \Magento\CustomerSegment\Model\Segment $segment
    ) {
        $connection = $this->getConnection();
        $customerTable = $this->getTable('magento_customersegment_customer');
        $segmentId = $segment->getId();
        $now = $this->dateTime->formatDate(time());
        $data = [];
        $count = 0;
        $connection->beginTransaction();
        try {
            foreach ($relatedCustomers as $customer) {
                $data[] = [
                    'segment_id' => $segmentId,
                    'customer_id' => $customer['entity_id'],
                    'website_id' => $customer['website_id'],
                    'added_date' => $now,
                    'updated_date' => $now,
                ];
                $count++;
                if ($count % 1000 == 0) {
                    $connection->insertMultiple($customerTable, $data);
                    $data = [];
                }
            }
            if (!empty($data)) {
                $connection->insertMultiple($customerTable, $data);
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        $connection->commit();

        return $this;
    }

    /**
     * Retrieve customers that where matched by segment and website id
     *
     * @param \Magento\CustomerSegment\Model\Segment $segment
     * @param array $websiteIds
     * @return \Generator
     */
    private function getRelatedCustomers($segment, $websiteIds): \Generator
    {
        $customerIds = [];
        foreach ($websiteIds as $websiteId) {
            if ($this->_configShare->isGlobalScope() && empty($customerIds)) {
                $customerIds = $segment->getDataByKey('is_remote') && $segment->getDataByKey('remote_code') ?
                    $this->getRemoteSatisfiedIds($segment->getDataByKey('remote_code'))
                    : $segment->getConditions()->getSatisfiedIds(null);
            } elseif ($this->_configShare->isWebsiteScope()) {
                $customerIds = $segment->getDataByKey('is_remote') && $segment->getDataByKey('remote_code') ?
                    $this->getRemoteSatisfiedIds($segment->getDataByKey('remote_code'))
                    : $segment->getConditions()->getSatisfiedIds($websiteId);
            }
            //get customers ids that satisfy conditions
            foreach ($customerIds as $customerId) {
                yield [
                    'entity_id' => $customerId,
                    'website_id' => $websiteId,
                ];
            }
        }
    }

    /**
     * @param string $remoteCode
     * @param null|int $websiteId
     * @return array
     */
    private function getRemoteSatisfiedIds($remoteCode, $websiteId=null)
    {
        $connection = $this->getConnection();
        $subSelect = $connection->select()
            ->from('eav_attribute', ['attribute_id'])
            ->where("attribute_code = 'integration_number' AND entity_type_id = 1");

        $sql = $connection->select()
            ->from(['main' => 'customer_entity'], ['entity_id' => 'main.entity_id'])
            ->join(
                ['cev' => 'customer_entity_varchar'],
                "main.entity_id = cev.entity_id AND cev.attribute_id = (" . $subSelect . ")" ,
                ['value' => 'cev.value']
            )
            ->where('cev.value IN (?)', $this->getRemoteCustomerIntgrationNumbers($remoteCode));
        if ($websiteId) {
            $sql->where("website_id = ?", $websiteId);
        }
        $ids = $connection->fetchCol($sql);
        return $ids;
    }

    /**
     * @param string $remoteCode
     * @return array
     */
    private function getRemoteCustomerIntgrationNumbers($remoteCode)
    {
        $connection = $this->getConnection();
        $sql = $connection->select()
            ->from('amore_gcrm_bannerd', ['cstmintgseq'])
            ->where("segcd = ?", $remoteCode);
        $integrationNumbers = $connection->fetchCol($sql);
        return $integrationNumbers;
    }

}
