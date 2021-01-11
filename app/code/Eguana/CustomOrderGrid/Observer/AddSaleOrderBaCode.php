<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 11/12/20
 * Time: 3:20 PM
 */
namespace Eguana\CustomOrderGrid\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Adding Customer BA Code in sales_order table
 *
 * Class AddSaleOrderBaCode
 */
class AddSaleOrderBaCode implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
    }

    /**
     * To add customer BA code in order's tables
     *
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        try {
            $customerId = $observer->getEvent()->getOrder()->getCustomerId();
            $customer = $this->customerRepository->getById($customerId);

            if ($customer->getId()) {
                $baCode = '';
                if ($customer->getCustomAttribute('ba_code')) {
                    $baCode = $customer->getCustomAttribute('ba_code')->getValue();
                }
                $observer->getEvent()->getOrder()->setData('customer_ba_code', $baCode);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error while adding ba code in order table:' . $e->getMessage());
        }
        return $this;
    }
}
