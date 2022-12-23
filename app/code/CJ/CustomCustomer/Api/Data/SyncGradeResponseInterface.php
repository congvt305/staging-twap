<?php

namespace CJ\CustomCustomer\Api\Data;

/**
 * Interface SyncGradeResponseInterface
 */
interface SyncGradeResponseInterface
{
    const CODE = 'code';

    const MESSAGE = 'message';

    const DATA = 'data';

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string|null $code
     * @return mixed
     */
    public function setCode(?string $code);

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param string|null $message
     * @return mixed
     */
    public function setMessage(?string $message);

    /**
     * @return \CJ\CustomCustomer\Api\Data\UpdateCustomerGroupResponseInterface[]
     */
    public function getData(): array;

    /**
     * @param \CJ\CustomCustomer\Api\Data\UpdateCustomerGroupResponseInterface[] $data
     * @return mixed
     */
    public function setData($data);
}
