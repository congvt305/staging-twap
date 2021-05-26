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
     * OrderManagement constructor.
     * @param Session $checkoutSession
     * @param RedInvoiceFactory $redInvoiceFactory
     * @param RedInvoiceRepositoryInterface $redInvoiceRepository
     * @param RedInvoiceLogger $redInvoicelogger
     */
    public function __construct(
        Session $checkoutSession,
        RedInvoiceFactory $redInvoiceFactory,
        RedInvoiceRepositoryInterface $redInvoiceRepository,
        RedInvoiceLogger $redInvoicelogger,
        Region $region
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->redInvoiceFactory = $redInvoiceFactory;
        $this->redInvoiceRepository = $redInvoiceRepository;
        $this->redInvoicelogger = $redInvoicelogger;
        $this->region = $region;
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
                $state = $this->checkoutSession->getState();
                $country = $this->checkoutSession->getCountry();
                $roadName = $this->checkoutSession->getRoadName();

                $stateName = $this->region->load($state);

                $model = $this->redInvoiceFactory->create();
                $model->setOrderId($orderId);
                $model->setIsApply($isApply);
                $model->setCompanyName($companyName);
                $model->setTaxCode($taxCode);
                $model->setState($stateName->getDefaultName());
                $model->setCountry($country);
                $model->setRoadName($roadName);
                $this->redInvoiceRepository->save($model);

                $message = 'Red invoice info after adding into database (eguana_red_invoice_data)';
                $redInvoiceInfo = [
                    'order_id' => $orderId,
                    'is_apply' => $isApply ? 'Yes' : 'No',
                    'company_name' => $companyName,
                    'tax_code' => $taxCode,
                    'state' => $state,
                    'country' => $country,
                    'road_name' => $roadName
                ];
                $this->redInvoicelogger->logRedInvoiceInfo($message, $redInvoiceInfo);
            }
        }
        return $order;
    }
}
