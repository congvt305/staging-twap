<?php

namespace CJ\CustomCustomer\Model\CustomerGroup;

use CJ\CustomCustomer\Api\Data\UpdateCustomerGroupResponseInterface;

/**
 * Class UpdateCustomerGroupResponse
 */
class UpdateCustomerGroupResponse
    extends \Magento\Framework\Model\AbstractExtensibleModel
    implements UpdateCustomerGroupResponseInterface
{

    /**
     * {@inheritDoc}
     */
    public function getCode(): string
    {
        return $this->getData(self::CODE);
    }

    /**
     * {@inheritDoc}
     */
    public function setCode(?string $code)
    {
        $this->setData(self::CODE, $code);
    }

    /**
     * {@inheritDoc}
     */
    public function getMessage(): string
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * {@inheritDoc}
     */
    public function setMessage(?string $message)
    {
        $this->setData(self::MESSAGE, $message);
    }

    /**
     * {@inheritDoc}
     */
    public function getGradeData(): array
    {
        return $this->getData(self::GRADE_DATA);
    }

    /**
     * {@inheritDoc}
     */
    public function setGradeData(?array $data)
    {
        $this->setData(self::GRADE_DATA, $data);
    }
}
