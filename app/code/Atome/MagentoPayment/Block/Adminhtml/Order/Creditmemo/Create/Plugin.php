<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Block\Adminhtml\Order\Creditmemo\Create;

class Plugin
{
    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @return mixed
     */
    public function aroundSetLayout($subject, \Closure $proceed, $layout)
    {
        $ret = $proceed($layout);
        $payment = $subject->getCreditmemo()->getOrder()->getPayment();
        if ($payment->getMethod() == \Atome\MagentoPayment\Model\PaymentGateway::METHOD_CODE) {
            // remove the  "Refund Offline" button in the Invoice => Credit Memo page
            $subject->unsetChild('submit_offline');
        }
        return $ret;
    }
}
