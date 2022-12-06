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
    public function getIntegrationSequence(): string
    {
        return $this->getData(self::INTEGRATION_SEQUENCE);
    }

    /**
     * {@inheritDoc}
     */
    public function setIntegrationSequence(?string $intgSeq)
    {
        $this->setData(self::INTEGRATION_SEQUENCE, $intgSeq);
    }

    /**
     * {@inheritDoc}
     */
    public function getGradeNm(): string
    {
        return $this->getData(self::GRADE_NM);
    }

    /**
     * {@inheritDoc}
     */
    public function setGradeNm(?string $gradeNM)
    {
        $this->setData(self::GRADE_NM, $gradeNM);
    }

    /**
     * {@inheritDoc}
     */
    public function getGradeCd(): string
    {
        return $this->getData(self::GRADE_CD);
    }

    /**
     * {@inheritDoc}
     */
    public function setGradeCd(?string $gradePrefix)
    {
        $this->setData(self::GRADE_CD, $gradePrefix);
    }

    /**
     * {@inheritDoc}
     */
    public function getScope(): string
    {
        return $this->getData(self::REQUEST_SCOPE);
    }

    /**
     * {@inheritDoc}
     */
    public function setScope(string $scope = 'default')
    {
        $this->setData(self::REQUEST_SCOPE, $scope);
    }
}
