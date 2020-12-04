<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 19/10/20
 * Time: 6:01 PM
 */
namespace Eguana\LinePay\Gateway\Http\Client;

use Eguana\LinePay\Gateway\Request\PaymentDataBuilder;

/**
 * Class TransactionRefund
 *
 * Process refund
 */
class TransactionRefund extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $storeId = $data['store_id'] ?? null;
        return $this->adapterFactory->create($storeId)
            ->refund($data['transaction_id'], $data[PaymentDataBuilder::AMOUNT], $storeId);
    }
}
