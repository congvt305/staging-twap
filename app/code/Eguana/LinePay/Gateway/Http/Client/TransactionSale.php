<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 6/10/20
 * Time: 3:18 PM
 */
namespace Eguana\LinePay\Gateway\Http\Client;

/**
 * Class TransactionSale confirm payment
 */
class TransactionSale extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $storeId = $data['store_id'] ?? null;
        // sending store id and other additional keys are restricted by LINEPAY API
        unset($data['store_id']);
        return $this->adapterFactory->create($storeId)->sale($data);
    }
}
