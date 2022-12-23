<?php

namespace CJ\CustomCustomer\Model\CustomerGroup;

/**
 * Class SyncGradeReqItem
 */
class SyncGradeReqItem
    extends \Magento\Framework\Model\AbstractExtensibleModel
    implements \CJ\CustomCustomer\Api\Data\SyncGradeReqItemInterface
{
    /**
     * {@inheritDoc}
     */
    public function getGradeData(): \CJ\CustomCustomer\Api\Data\CustomerDataInterface
    {
        return $this->getData(self::GRADE_DATA);
    }

    /**
     * {@inheritDoc}
     */
    public function setGradeData(\CJ\CustomCustomer\Api\Data\CustomerDataInterface $customerData)
    {
        return $this->setData(self::GRADE_DATA, $customerData);
    }
}
