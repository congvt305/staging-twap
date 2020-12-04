<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 15/10/20
 * Time: 4:11 PM
 */
namespace Eguana\LinePay\Gateway\Http\Client;

/**
 * Class TransactionCaptureSale capture payment
 */
class TransactionCaptureSale extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $storeId = $data['store_id'] ?? null;
        // sending store id and other additional keys are restricted by LINE Pay API
        unset($data['store_id']);
        return $this->adapterFactory->create($storeId)->capture($data);
    }
}
