<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/27/20
 * Time: 4:12 PM
 */

namespace Eguana\GWLogistics\Model\Service;


class SmsSender
{
    /**
     * @var \Eguana\StoreSms\Api\SmsManagementInterface
     */
    private $smsManagement;
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $helper;

    public function __construct(
        \Eguana\StoreSms\Api\SmsManagementInterface $smsManagement,
        \Eguana\GWLogistics\Helper\Data $helper
    )
    {
        $this->smsManagement = $smsManagement;
        $this->helper = $helper;
    }

    public function sendSms(\Magento\Rma\Api\Data\RmaInterface $rma, string $returnOrderNumber)
    {
        $number = $rma->getData('customer_custom_phone');
        if ($number) {
            $message = 'test message';
        }
        $this->smsManagement->sendMessage($number, $$message);
    }

}
