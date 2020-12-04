<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 10/11/20
 * Time: 11:30 AM
 */
namespace Eguana\LinePay\Observer;

use Magento\Framework\Event\Observer as ObserverAlias;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddAdditionalDataObserver
 *
 * Add additional data in current quote
 */
class AddAdditionalDataObserver implements ObserverInterface
{
    /**
     * Add additional data in current quote
     * @param ObserverAlias $observer
     */
    public function execute(ObserverAlias $observer)
    {
        $input = $observer->getData('input');
        $data = $observer->getData('payment');
        $method = $input->getData('method');
        $additionalData = $input->getData('additional_data');
        $quote = $data->getQuote();
        if ($method == 'linepay_payment') {
            $additionalData["method_title"] = $method;
            $quote->getPayment()->setAdditionalInformation(
                'raw_details_info',
                $additionalData
            );
            $quote->getPayment()->save();
        }
    }
}
