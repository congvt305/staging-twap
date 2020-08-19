<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 8/20/20
 * Time: 7:10 AM
 */

namespace Eguana\EInvoice\Block\Adminhtml\Order\Creditmemo\View;


use Magento\Framework\Serialize\Serializer\Json;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\View\Form
{
    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct(
        Json $jsonSerializer,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->jsonSerializer = $jsonSerializer;
    }

    public function canShowInvalidateEInvoiceButton()
    {
        $payment = $this->getOrder()->getPayment();
        return  $this->hasEInvoice($payment) && !$this->hasInvalidateInvoice($payment);
    }

    public function getInvalidateEInvoiceUrl()
    {
        return $this->getUrl('eguana_einvoice/einvoice/invalidate', ['creditmemo_id' => $this->getCreditmemo()->getEntityId()]);
    }

    private function hasEInvoice($payment)
    {
        $addtionalData =$payment->getAdditionalData();
        if (!$addtionalData) {
            return false;
        }
        $addtionalData = $this->jsonSerializer->unserialize($addtionalData);
        return isset($addtionalData['InvoiceNumber']);
    }

    private function hasInvalidateInvoice($payment)
    {
        $ecpayInvoiceInvalidateData =$payment->getData('ecpay_invoice_invalidate_data');
        if (!$ecpayInvoiceInvalidateData) {
            return false;
        }
        $ecpayInvoiceInvalidateData = $this->jsonSerializer->unserialize($ecpayInvoiceInvalidateData);
        return (isset($ecpayInvoiceInvalidateData['RtnCode']) && $ecpayInvoiceInvalidateData['RtnCode'] === '1');

    }

}
