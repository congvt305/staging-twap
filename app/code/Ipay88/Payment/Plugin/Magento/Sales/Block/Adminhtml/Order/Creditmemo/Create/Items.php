<?php

namespace Ipay88\Payment\Plugin\Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create;

class Items
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
        if ($payment->getMethod() == \Ipay88\Payment\Model\Ui\ConfigProvider::CODE) {
            $subject->unsetChild('submit_offline');
            $subject->unsetChild('submit_button');
        }
        return $ret;
    }
}
