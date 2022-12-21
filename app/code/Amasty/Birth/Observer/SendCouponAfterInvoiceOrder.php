<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Occasion Coupons for Magento 2
*/

namespace Amasty\Birth\Observer;

use Magento\Framework\Event\ObserverInterface;

class SendCouponAfterInvoiceOrder extends \Amasty\Birth\Model\Sender\AbstractSender implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order) {
            return $this;
        }

        $type = 'order_placed';
        
        if ($this->helper->getModuleConfig($type . '/enabled')) {
            $customer = $order->getCustomer();
            
            try {
                if (!$customer || !$customer->getId()) {
                    $customer = $this->customerFactory->create();
                    $customer->setWebsiteId($this->helper->getCurrentWebsiteId());
                    $customer->setEmail($order->getData('customer_email'));
                    $customer->setFirstname(__('Dear Friend'));
                    $customer->setGroupId(0);
                    $customer->setId(0);
                    $customer->setStore($order->getStore());
                    $customer->setStoreId($order->getStoreId());
                }
            } catch (\Exception $exc) {
                $this->messageManager->addExceptionMessage($exc, __('Can\'t send order placed coupon'));
            }
            
            $this->_sendMailToCustomer($customer, $type);
        }
    }
}
