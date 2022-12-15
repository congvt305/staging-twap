<?php

namespace CJ\CustomCustomer\Api\Data;

/**
 * Interface UpdateCustomerGroupResponseInterface
 */
interface UpdateCustomerGroupResponseInterface
{
    const SUCCESS = 'success';

    const CODE = 'code';

    const MESSAGE = 'message';

    /**
     * @return bool
     */
    public function getSuccess(): bool;

    /**
     * @param bool|null $isSuccess
     * @return $this
     */
    public function setSuccess(?bool $isSuccess);

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string|null $code
     * @return $this
     */
    public function setCode(?string $code);

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param string|null $message
     * @return $this
     */
    public function setMessage(?string $message);
}
