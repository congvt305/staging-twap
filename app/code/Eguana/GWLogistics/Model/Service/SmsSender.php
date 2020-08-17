<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/27/20
 * Time: 4:12 PM
 */

namespace Eguana\GWLogistics\Model\Service;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class SmsSender
{
    /**
     * @var \Eguana\StoreSms\Api\SmsManagementInterface
     */
    private $smsManagement;
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    private $templateFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var OrderAddressRepositoryInterface
     */
    private $orderAddressRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;


    public function __construct(
        \Eguana\StoreSms\Api\SmsManagementInterface $smsManagement,
        \Eguana\GWLogistics\Helper\Data $helper,
        \Magento\Email\Model\TemplateFactory $templateFactory,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        OrderAddressRepositoryInterface $orderAddressRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Psr\Log\LoggerInterface $logger
     )
    {
        $this->smsManagement = $smsManagement;
        $this->helper = $helper;
        $this->templateFactory = $templateFactory;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function sendSms(\Magento\Rma\Api\Data\RmaInterface $rma, string $returnOrderNumber)
    {
        if (!$this->helper->getSendSmsActive()) {
            return;
        }
        try {
            $number = $rma->getData('customer_custom_phone') ??
                $this->getOrderTelephone($rma->getOrderId());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return;
        }

        if ($number) {
            try {
                $message = $this->getMessage($rma, $returnOrderNumber);
                $this->logger->info('gwlogistics | SMS message for reverse order', [$message]);
                $this->logger->info('gwlogistics | SMS number for reverse order', [$number]);
                $storeId = $rma->getStoreId();
                $this->smsManagement->sendMessage($number, $message, $storeId);
            } catch (NoSuchEntityException $e) {
                $this->logger->critical($e->getMessage());
            } catch (LocalizedException $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        return;
    }

    /**
     * @param \Magento\Rma\Api\Data\RmaInterface$rma
     * @param  string $returnOrderNumber
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getMessage($rma, $returnOrderNumber)
    {
        //sms_prefix=$sms_prefix customer=$customer rtn_order_number=$rtn_order_number
        $smsPrefix = $this->helper->getSmsPrefix($rma->getStoreId());
        $customer = $this->customerRepository->getById($rma->getCustomerId());
        $customerName = $customer->getLastname() . $customer->getFirstname();
        $templateIdentifier = $this->helper->getMessageTemplate($rma->getStoreId());
        /** @var \Magento\Email\Model\Template $templateModel */
        $templateModel = $this->templateFactory->create();
        $params = [ 'sms_prefix' => $smsPrefix, 'customer'=> $customerName,'rtn_order_number' => $returnOrderNumber];
        $templateModel->setDesignConfig(['area' => 'frontend', 'store' => $rma->getStoreId()]);
        $templateModel->loadDefault($templateIdentifier);

        return $templateModel->getProcessedTemplate($params);
    }

    private function getOrderTelephone(int $orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('parent_id', $orderId)
            ->addFilter('address_type', 'shipping')
            ->create();
        $orderAddress = $this->orderAddressRepository->getList($searchCriteria)->getItems();
        $orderAddress = reset($orderAddress);
        return $orderAddress->getTelephone();
    }

}
