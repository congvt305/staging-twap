<?php

namespace CJ\CustomCustomer\Model\CustomerGroup;

use CJ\CustomCustomer\Api\Data\CustomerDataInterface;

/**
 * Class CustomerData
 */
class CustomerData
    extends \Magento\Framework\Model\AbstractExtensibleModel
    implements CustomerDataInterface
{

    /**
     * {@inheritDoc}
     */
    public function getIntegrationNumber(): string
    {
        return $this->getData(self::INTEGRATION_NUMBER);
    }

    /**
     * {@inheritDoc}
     */
    public function setIntegrationNumber(?string $integrationNumber)
    {
        $this->setData(self::INTEGRATION_NUMBER, $integrationNumber);
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentGrade(): string
    {
        return $this->getData(self::CURRENT_GRADE);
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrentGrade(?string $currentGrade)
    {
        $this->setData(self::CURRENT_GRADE, $currentGrade);
    }
}
