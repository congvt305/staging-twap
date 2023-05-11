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
    public function getCstmIntgSeq(): string
    {
        return $this->getData(self::CSTM_INTG_SEQ);
    }

    /**
     * {@inheritDoc}
     */
    public function setCstmIntgSeq(?string $intgSeq)
    {
        return $this->setData(self::CSTM_INTG_SEQ, $intgSeq);
    }

    /**
     * {@inheritDoc}
     */
    public function getCstmGradeNM(): string
    {
        return $this->getData(self::CSTM_GRADE_N_M);
    }

    /**
     * {@inheritDoc}
     */
    public function setCstmGradeNM(?string $gradeNM)
    {
        return $this->setData(self::CSTM_GRADE_N_M, $gradeNM);
    }

    /**
     * {@inheritDoc}
     */
    public function getCstmGradeCD(): string
    {
        return $this->getData(self::CSTM_GRADE_C_D);
    }

    /**
     * {@inheritDoc}
     */
    public function setCstmGradeCD(?string $gradePrefix)
    {
        return $this->setData(self::CSTM_GRADE_C_D, $gradePrefix);
    }

}
