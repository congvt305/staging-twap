<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: mobeen
 * Date: 13/7/20
 * Time: 4:13 PM
 */

namespace Eguana\StoreSms\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Eguana\StoreSms\Helper\Data;
use Eguana\StoreSms\Model\SmsSender;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\Message\ManagerInterface;
use Eguana\StoreSms\Model\CountryCode;
use Magento\Framework\App\State;
use Magento\Sales\Api\OrderRepositoryInterface;

class CreditMemoRefundSaveAfter implements ObserverInterface
{
    /**
     * constants
     */
    const REMOVE_SPECIAL_NUMBER = '/[^a-z A-Z_ 0-9]/s';
    const REMOVE_SPACE_NUMBER = '/\s+/';

    /**
     * @var Data
     */
    private $data;

    /**
     * @var SmsSender
     */
    private $sendNotification;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var ManagerInterface
     */
    private $messageManagerInterface;

    /**
     * @var CountryCode
     */
    private $countryCode;

    /**
     * @var State
     */
    private $state;

    /**
     * OrderStatusSaveAfter constructor.
     * @param Data $data
     * @param SmsSender $sendNotification
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param Config $eavConfig
     * @param ManagerInterface $messageManagerInterface
     * @param CountryCode $countryCode
     * @param State $state
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Data $data,
        SmsSender $sendNotification,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        Config $eavConfig,
        ManagerInterface $messageManagerInterface,
        CountryCode $countryCode,
        State $state,
        OrderRepositoryInterface $orderRepository

    ) {
        $this->data = $data;
        $this->sendNotification = $sendNotification;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->eavConfig = $eavConfig;
        $this->messageManagerInterface = $messageManagerInterface;
        $this->countryCode = $countryCode;
        $this->state = $state;
        $this->orderRepository = $orderRepository;

    }

    /**
     * It will check whether credit memo refund is requested if yes then send the refund SMS
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $creditmemo = $observer->getEvent()->getCreditmemo();
            $order = $this->orderRepository->get($creditmemo->getOrderId());
            $storeId = $order->getData('store_id');
            $storeName = $this->storeManager->getStore($storeId)->getName();
            $smsModuleActive = $this->data->getActivation($storeId);
            $storePhoneNumber = $this->data->getStorePhoneNumber($storeId);
            if ($smsModuleActive) {
                $newStatus = 'refund';
                $isActive = $this->data->getOrderStatus($newStatus);
                if ($isActive) {
                    $shippingAddress = $order->getShippingAddress();
                    if ($shippingAddress == null) {
                        $shippingAddress = $order->getBillingAddress();
                    }
                    $countryCode = $this->getCountryCode($storeId);
                    $shippingTelephone = $shippingAddress->getData('telephone');
                    $mobilenumber = preg_replace(
                        self::REMOVE_SPECIAL_NUMBER,
                        '',
                        $shippingTelephone
                    );
                    $mobilenumber = preg_replace(self::REMOVE_SPACE_NUMBER, '', $mobilenumber);
                    $telephone = $this->sendNotification->getPhoneNumberWithCode($mobilenumber, $countryCode);
                    $firstName = $shippingAddress->getData('firstname');
                    $orderId = $order->getIncrementId();
                    $templatePath = $this->data->getTemplateIdentifer($newStatus, $storeId);
                    $orderNotification = $this->sendNotification
                        ->getOrderNotification($storeId, $templatePath, $firstName, $orderId, $storeName, $storePhoneNumber);
                    if ($this->state->getAreaCode() != 'adminhtml' && $order->getExportProcessed()) {
                        return;
                    } else {
                        $result = $this->sendNotification->sendMessageByApi($orderNotification, $telephone, $storeId);
                        if (!$result) {
                            $this->messageManagerInterface->addErrorMessage(__('Please check your api credentials'));
                        }
                    }
                    $order->setExportProcessed(true);

                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * By default drop down attribute have the value and we need the label of that value.
     * @param $storeId
     * @return string
     */
    private function getCountryCode($storeId)
    {
        $result = '';
        try {
            $countryInformation = $this->countryCode->getCountryCallCode();
            $result = $countryInformation[$this->data->getCurrentCountry($storeId)]['code'];

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }


}
