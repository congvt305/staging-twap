<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 2:58 PM
 */
namespace Eguana\CustomCheckout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * This class is consists of observer which is used to redirect at checkout page
 * Class GoToCheckout
 */
class GoToCheckout implements ObserverInterface
{
    /**
     * @var UrlInterface
     */
    private $urlinterface;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * GoToCheckout constructor.
     * @param UrlInterface $urlinterface
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlInterface $urlinterface,
        StoreManagerInterface $storeManager
    ) {
        $this->urlinterface = $urlinterface;
        $this->storeManager = $storeManager;
    }

    /**
     * This observer is used to skip the cart page if the store is vietnam
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $storeId = $this->getStoreId();
        if ($storeId == "8") {
            $observer->getRequest()->setParam('return_url', $this->urlinterface->getUrl('checkout'));
            return $this;
        } else {
            return $this;
        }
    }

    /**
     * Get store identifier
     * @return  int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
