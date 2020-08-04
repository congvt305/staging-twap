<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 8/4/20
 * Time: 4:13 PM
 */

namespace Eguana\EInvoice\Block\Adminhtml\Order\Invoice\View;


class Form extends \Magento\Sales\Block\Adminhtml\Order\Invoice\View\Form
{
    public function canShowCreateEInvoiceButton()
    {
//        return  ($this->getOrder()->getShippingMethod() === 'gwlogistics_CVS') && ($this->getInvoice()->getData('all_pay_logistics_id') === NULL);
        return  true;
    }

    public function getCreateEInvoiceUrl()
    {
        return $this->getUrl('eguana_einvoice/einvoice/create', ['invoice_id' => $this->getInvoice()->getEntityId()]);
    }

}
