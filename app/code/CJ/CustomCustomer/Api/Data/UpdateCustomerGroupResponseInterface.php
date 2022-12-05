<?php

namespace CJ\CustomCustomer\Api\Data;

interface UpdateCustomerGroupResponseInterface
{
    const CODE = 'code';

    const MESSAGE = 'message';

    const GRADE_DATA = 'grade_data';

    public function getCode(): string;

    public function setCode(?string $code);

    public function getMessage(): string;

    public function setMessage(?string $message);

    public function getGradeData():array;

    public function setGradeData(?array $data);
}
