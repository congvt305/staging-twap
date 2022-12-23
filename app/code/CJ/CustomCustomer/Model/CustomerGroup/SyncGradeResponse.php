<?php

namespace CJ\CustomCustomer\Model\CustomerGroup;

/**
 * Class SyncGradeResponse
 */
class SyncGradeResponse
    implements \CJ\CustomCustomer\Api\Data\SyncGradeResponseInterface
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var \CJ\CustomCustomer\Api\Data\CustomerDataInterface[]
     */
    protected $data;

    /**
     * {@inheritDoc}
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * {@inheritDoc}
     */
    public function setCode(?string $code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * {@inheritDoc}
     */
    public function setMessage(?string $message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
}
