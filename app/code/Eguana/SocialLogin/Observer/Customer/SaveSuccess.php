<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 9/12/20
 * Time: 7:06 PM
 */

namespace Eguana\SocialLogin\Observer\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Class SaveSuccess
 *
 * Save line message custom attributes
 */
class SaveSuccess implements ObserverInterface
{

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * SaveSuccess constructor.
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        RequestInterface $request,
        LoggerInterface $logger,
        CustomerFactory $customerFactory
    ) {
        $this->request                           = $request;
        $this->logger                            = $logger;
        $this->customerFactory                   = $customerFactory;
    }

    /**
     * Observer called on successfull customer registration
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        try {
            /**
             * @var Customer $newCustomerData
             */
            $newCustomerData = $observer->getEvent()->getData('customer_data_object');
            $postValue = $this->request->getParam('customer');
            if (isset($postValue['line_id']) && isset($postValue['line_message_agreement'])) {
                $customer = $this->customerFactory->create()->load($newCustomerData->getId());
                $customer->setData('line_id', $postValue['line_id']);
                $customer->setData('line_message_agreement', $postValue['line_message_agreement']);
                $customer->save();
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
