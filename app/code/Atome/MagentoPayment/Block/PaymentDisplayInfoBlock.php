<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Block;

use Atome\MagentoPayment\Helper\PaymentHelper;
use Atome\MagentoPayment\Model\PaymentGateway;
use Magento\Framework\View\Element\Template;

class PaymentDisplayInfoBlock extends \Magento\Payment\Block\Info
{
    protected $paymentHelper;

    public function __construct(
        Template\Context $context,
        PaymentHelper $paymentHelper
    ) {
        parent::__construct($context);
        $this->paymentHelper = $paymentHelper;
    }

    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $data = [];
        $info = $this->getInfo();

        if ($info->getAdditionalInformation()) {
            foreach ($info->getAdditionalInformation() as $field => $value) {
                if (in_array($field, [PaymentGateway::PAYMENT_DEBUG_SECRET, PaymentGateway::MERCHANT_REFERENCE_ID])) {
                    continue;
                } else if ($field === PaymentGateway::PAYMENT_AMOUNT_FORMATTED) {
                    $data['Atome payment amount'] = number_format($this->paymentHelper->reverseFormatAmount($value), 2);
                } else {
                    $beautifiedFieldName = str_replace('_', ' ', ucwords(trim(preg_replace('/(?<=\\w)(?=[A-Z])/', " $1", $field))));
                    $data[__($beautifiedFieldName)->getText()] = $value;
                }
            }
        }
        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
