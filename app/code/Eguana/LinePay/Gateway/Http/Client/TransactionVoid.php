<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 19/10/20
 * Time: 1:44 PM
 */
namespace Eguana\LinePay\Gateway\Http\Client;

/**
 * Class TransactionVoid
 *
 * Process void payment
 */
class TransactionVoid extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $storeId = $data['store_id'] ?? null;

        return $this->adapterFactory->create($storeId)->void($data);
    }
}
