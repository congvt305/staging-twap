<?php

namespace CJ\AmastyCheckoutCore\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Psr\Log\LoggerInterface;

class QuoteSubmitBefore implements ObserverInterface
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @param LoggerInterface $logger
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        LoggerInterface $logger,
        StoreRepositoryInterface $storeRepository
    )
    {
        $this->_logger = $logger;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @param Observer $observer
     * @return QuoteSubmitBefore
     */
    public function execute(Observer $observer)
    {
        try {
            $quote = $observer->getQuote();
            $order = $observer->getOrder();
            if ($this->getMYSWSStoreId() == $order->getStoreId()) {
                $order->getShippingAddress()->setCountryPosCode($quote->getCountryPosCode());
                $order->getBillingAddress()->setCountryPosCode($quote->getCountryPosCode());
                $order->setPackageOption($quote->getPackageOption());
            }
            return $this;
        } catch (\Exception $exception) {
            $this->_logger->critical('Quote Submit Error: ' . $exception->getMessage());
            return $this;
        }

    }

    /**
     * @return int|null
     */
    public function getMYSWSStoreId()
    {
        try {
            $store = $this->storeRepository->get(self::MY_SWS_STORE_CODE);
            return $store->getId();
        } catch (\Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }
        return null;
    }
}
