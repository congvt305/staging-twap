<?php

namespace CJ\AmastyCheckoutCore\Plugin\Magento\Checkout\Controller\Cart;

use Magento\Store\Model\StoreManagerInterface;

class Index
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        StoreManagerInterface $storeManager
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Checkout\Controller\Cart\Index $subject
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeExecute(\Magento\Checkout\Controller\Cart\Index $subject)
    {
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $quote = $this->_checkoutSession->getQuote();
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            if ($shippingMethod) {
                $quote->getShippingAddress()->setShippingMethod(null);
                $quote->collectTotals();
                $quote->save();
            }
        }
    }
}
