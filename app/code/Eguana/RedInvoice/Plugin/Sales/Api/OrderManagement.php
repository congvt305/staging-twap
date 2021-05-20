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

use Magento\Checkout\Model\Session;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Eguana\RedInvoice\Model\RedInvoiceFactory;
use Eguana\RedInvoice\Api\RedInvoiceRepositoryInterface;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
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
     * OrderManagement constructor.
     * @param Session $checkoutSession
     * @param RedInvoiceFactory $redInvoiceFactory
     * @param RedInvoiceRepositoryInterface $redInvoiceRepository
     */
    public function __construct(
        Session $checkoutSession,
        RedInvoiceFactory $redInvoiceFactory,
        RedInvoiceRepositoryInterface $redInvoiceRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->redInvoiceFactory = $redInvoiceFactory;
        $this->redInvoiceRepository = $redInvoiceRepository;
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
        $orderId = $order->getEntityId();
        if ($orderId) {
            $isApply = $this->checkoutSession->getIsApply();
            if ($isApply) {
                $companyName = $this->checkoutSession->getCompanyName();
                $taxCode = $this->checkoutSession->getTaxCode();
                $state = $this->checkoutSession->getState();
                $country = $this->checkoutSession->getCountry();
                $roadName = $this->checkoutSession->getRoadName();

                $model = $this->redInvoiceFactory->create();
                $model->setOrderId($orderId);
                $model->setIsApply($isApply);
                $model->setCompanyName($companyName);
                $model->setTaxCode($taxCode);
                $model->setState($state);
                $model->setCountry($country);
                $model->setRoadName($roadName);
                $this->redInvoiceRepository->save($model);
            }
        }
        return $order;
    }
}
