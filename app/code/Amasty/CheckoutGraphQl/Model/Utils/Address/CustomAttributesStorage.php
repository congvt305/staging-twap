<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Utils\Address;

use Amasty\CheckoutCore\Model\OrderCustomFields;

class CustomAttributesStorage
{
    /**
     * @var array
     */
    private $orderCustomFields = [];

    /**
     * @return OrderCustomFields[]
     */
    public function getData(): array
    {
        return $this->orderCustomFields;
    }

    /**
     * @param OrderCustomFields[] $fields
     * @return void
     */
    public function setData(array $fields): void
    {
        $this->orderCustomFields = $fields;
    }
}
