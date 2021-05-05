<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/08/12
 * Time: 10:47 AM
 */

namespace Ecpay\Ecpaypayment\Block\Info;

use Magento\Framework\View\Element\Template;

class Instructions extends \Magento\Payment\Block\Info\Instructions
{
    /**
     * @var string
     */
    protected $_template = 'Ecpay_Ecpaypayment::info/instructions.phtml';

    public function getEcpayPaymentMethod()
    {
        $additionalInfo = $this->getInfo()->getAdditionalInformation();
        $ecpayPaymentMethod = '';
        $ecpayChoosenPayment = '';

        if (isset($additionalInfo["raw_details_info"]['ecpay_choosen_payment'])
            && !empty($additionalInfo["raw_details_info"]['ecpay_choosen_payment'])) {
            $ecpayChoosenPayment = $rawDetailsInfo["ecpay_choosen_payment"];
        }

        if (isset($additionalInfo["ecpay_choosen_payment"])) {
            $ecpayChoosenPayment = $additionalInfo["ecpay_choosen_payment"];
        }

        if (! $ecpayChoosenPayment) {
            return $ecpayPaymentMethod;
        }

        if (strpos($ecpayChoosenPayment, 'credit') !== false) {
            $ecpayPaymentMethod = '信用卡付款';
        } elseif ($ecpayChoosenPayment == 'atm') {
            $ecpayPaymentMethod = 'ATM轉帳';
        }

        return $ecpayPaymentMethod;
    }
}
