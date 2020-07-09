<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/8/20
 * Time: 3:36 PM
 */

namespace Eguana\GWLogistics\Plugin\Sales;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order\ShipmentRepository;

class ShipmentRepositoryPlugin
{
    /**
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $subject
     * @param $result
     * @param int $id
     */
    public function afterGet(\Magento\Sales\Api\ShipmentRepositoryInterface $subject, $result)
    {
        if ($result->getData('all_pay_logistics_id')) {
            $extensionAttributes = $result->getExtensionAttributes();
            try {
                $extensionAttributes->setAllPayLogisticsId($result->getData('all_pay_logistics_id'));
            } catch (\Exception $e) {
                $extensionAttributes->setAllPayLogisticsId(null);
            }
            $result->setExtensionAttributes($extensionAttributes);
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\ShipmentSearchResultInterface $result
     * @param SearchCriteriaInterface $searchCriteria
     */
    public function afterGetList(\Magento\Sales\Api\ShipmentRepositoryInterface $subject, $result, SearchCriteriaInterface $searchCriteria)
    {
        foreach ($result->getItems() as $shipment) {
            $this->afterGet($subject, $shipment);
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Model\Order\ShipmentRepository $subject
     * @param ShipmentInterface $entity
     * @return array
     */
    public function beforeSave(\Magento\Sales\Model\Order\ShipmentRepository $subject, ShipmentInterface $entity)
    {
        $extensionAttributes = $entity->getExtensionAttributes();
        $allPayLogisticsId = $extensionAttributes->getAllPayLogisticsId();
        if ($allPayLogisticsId) {
            $entity->setData('all_pay_logistics_id', $allPayLogisticsId);
        }
        return [$entity];
    }

}
