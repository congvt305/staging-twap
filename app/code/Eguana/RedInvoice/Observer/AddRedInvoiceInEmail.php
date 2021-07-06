<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 1/6/21
 * Time: 5:03 PM
 */
namespace Eguana\RedInvoice\Observer;

use Eguana\RedInvoice\Api\RedInvoiceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * This observer class is used to add the red invoice information into email
 * Class AddRedInvoiceInEmail
 */
class AddRedInvoiceInEmail implements ObserverInterface
{
    /**
     * @var RedInvoiceRepositoryInterface
     */
    private $redInvoiceRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * AddRedInvoiceInEmail constructor.
     * @param RedInvoiceRepositoryInterface $redInvoiceRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        RedInvoiceRepositoryInterface $redInvoiceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->redInvoiceRepository = $redInvoiceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * This method is used to observe the email template vars and add red inovoice vars into email
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $transport = $observer->getTransport();
        $order = $transport['order'];
        $orderId = $order->getEntityId();

        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'order_id',
            $orderId,
            'eq'
        )->create();
        $redInvoice = $this->redInvoiceRepository->getList($searchCriteria);
        $redInvoiceData = current($redInvoice->getItems());
        if ($redInvoiceData) {
            $currentRedInvoice = $redInvoiceData->getData();
            $transport['redInvoiceId'] = $currentRedInvoice['id'];
            $transport['companyName'] = $currentRedInvoice['company_name'];
            $transport['taxCode'] = $currentRedInvoice['tax_code'];
            $transport['redInvoiceState'] = $currentRedInvoice['state'];
            $transport['redInvoiceCity'] = $currentRedInvoice['city'];
            $transport['roadName'] = $currentRedInvoice['road_name'];
            return $this;
        }
        return $this;
    }
}
