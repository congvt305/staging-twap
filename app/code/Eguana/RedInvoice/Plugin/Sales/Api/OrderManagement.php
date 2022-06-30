<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 10:10 AM
 */
namespace Eguana\RedInvoice\Plugin\Sales\Api;

use Eguana\RedInvoice\Model\RedInvoiceLogger;
use Magento\Checkout\Model\Session;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Eguana\RedInvoice\Model\RedInvoiceFactory;
use Eguana\RedInvoice\Api\RedInvoiceRepositoryInterface;
use Magento\Directory\Model\Region;
use Eguana\Directory\Model\City;
use Eguana\Directory\Model\Ward;

/**
 * This class is consists of after plugin for process to add the red invoice
 * information into database
 * Class OrderManagement
 */
class OrderManagement
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var RedInvoiceFactory
     */
    private $redInvoiceFactory;

    /**
     * @var RedInvoiceRepositoryInterface
     */
    private $redInvoiceRepository;

    /**
     * @var RedInvoiceLogger
     */
    private $redInvoicelogger;

    /**
     * @var Region
     */
    private $region;

    /**
     * @var City
     */
    private $city;

    /**
     * @var Ward
     */
    private $ward;

    /**
     * OrderManagement constructor.
     * @param Session $checkoutSession
     * @param RedInvoiceFactory $redInvoiceFactory
     * @param RedInvoiceRepositoryInterface $redInvoiceRepository
     * @param RedInvoiceLogger $redInvoicelogger
     * @param Region $region
     * @param City $city
     */
    public function __construct(
        Session $checkoutSession,
        RedInvoiceFactory $redInvoiceFactory,
        RedInvoiceRepositoryInterface $redInvoiceRepository,
        RedInvoiceLogger $redInvoicelogger,
        Region $region,
        City $city,
        Ward $ward
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->redInvoiceFactory = $redInvoiceFactory;
        $this->redInvoiceRepository = $redInvoiceRepository;
        $this->redInvoicelogger = $redInvoicelogger;
        $this->region = $region;
        $this->city = $city;
        $this->ward = $ward;
    }

    /**
     * @param OrderManagementInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlace(
        OrderManagementInterface $subject,
        OrderInterface $order
    ) {
        $redInvoiceInfo = [];
        $orderId = $order->getEntityId();
        if ($orderId) {
            $isApply = $this->checkoutSession->getIsApply();
            if ($isApply) {
                $companyName = $this->checkoutSession->getCompanyName();
                $taxCode = $this->checkoutSession->getTaxCode();
                $stateId = $this->checkoutSession->getState();
                $cityId = $this->checkoutSession->getCity();
                $email = $this->checkoutSession->getEmail();
                $wardId = $this->checkoutSession->getWard();
                $roadName = $this->checkoutSession->getRoadName();

                $stateInfo = $this->region->load($stateId);

                $cityInfo = $this->city->load($cityId);

                $wardInfo = $this->ward->load($wardId);

                $model = $this->redInvoiceFactory->create();
                $model->setOrderId($orderId);
                $model->setIsApply($isApply);
                $model->setCompanyName($companyName);
                $model->setTaxCode($taxCode);
                $model->setEmail($email);
                $model->setState($stateInfo->getDefaultName());
                $model->setCity($cityInfo->getDefaultName());
                $model->setWard($wardInfo->getDefaultName());
                $model->setRoadName($roadName);
                $this->redInvoiceRepository->save($model);

                $message = 'Red invoice info after adding into database (eguana_red_invoice_data)';
                $redInvoiceInfo = [
                    'order_id' => $orderId,
                    'is_apply' => $isApply ? 'Yes' : 'No',
                    'company_name' => $companyName,
                    'tax_code' => $taxCode,
                    'email' => $email,
                    'state' => $stateId,
                    'city' => $cityId,
                    'ward' => $wardId,
                    'road_name' => $roadName
                ];
                $this->redInvoicelogger->logRedInvoiceInfo($message, $redInvoiceInfo);
            }
        }
        return $order;
    }
}
