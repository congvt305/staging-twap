<?php

namespace Amore\CustomerRegistration\Observer\Customer;

use Amore\CustomerRegistration\Model\POSLogger;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class BeforeAddressSaveObserver
 * @package Amore\CustomerRegistration\Observer\Customer
 */
class BeforeAddressSaveObserver implements ObserverInterface
{
    /**
     * @var POSLogger
     */
    private $logger;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $requestApi;

    /**
     * @var \Eguana\Directory\Helper\Data
     */
    private $cityHelper;

    /**
     * @param RequestInterface $request
     * @param POSLogger $logger
     * @param \Magento\Framework\Webapi\Rest\Request $requestApi
     * @param \Eguana\Directory\Helper\Data $cityHelper
     */
    public function __construct(
        RequestInterface $request,
        POSLogger $logger,
        \Magento\Framework\Webapi\Rest\Request $requestApi,
        \Eguana\Directory\Helper\Data $cityHelper
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->requestApi = $requestApi;
        $this->cityHelper = $cityHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        try {
            /** @var \Magento\Customer\Model\Address $address */
            $address = $observer->getData('customer_address');
            $regionId = $address->getRegionId();
            $cityId = $this->requestApi->getParam('city_id') ? $this->requestApi->getParam('city_id') : $this->request->getParam('city_id');
            if ($cityId && $regionId) {
                $cities = $this->cityHelper->getCityData();
                $regionCities = $cities[$regionId];
                foreach ($regionCities as $regionCity) {
                    if ($regionCity['code'] == $cityId) {
                        $address->setCity($regionCity['name']);
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->addExceptionMessage($e->getMessage());
        }
    }
}
