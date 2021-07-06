<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 20/5/21
 * Time: 10:02 PM
 */
namespace Eguana\RedInvoice\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Eguana\RedInvoice\Api\RedInvoiceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * This class used to get the red invoice data
 * Class RedInvoiceDetails
 */
class RedInvoiceDetails implements ArgumentInterface
{
    /**
     * @var RedInvoiceRepositoryInterface
     */
    private $redInvoiceRepository;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * RedInvoiceDetails constructor.
     * @param RedInvoiceRepositoryInterface $redInvoiceRepository
     * @param Http $request
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        RedInvoiceRepositoryInterface $redInvoiceRepository,
        Http $request,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->redInvoiceRepository = $redInvoiceRepository;
        $this->request = $request;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * This method is used to get the red invoice details
     * @return mixed
     */
    public function getRedInvoice()
    {
        $orderId = $this->getOrderId();
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'order_id',
            $orderId,
            'eq'
        )->create();
        $redInvoice = $this->redInvoiceRepository->getList($searchCriteria);
        if ($redInvoice->getItems()) {
            return current($redInvoice->getItems());
        } else {
            return $redInvoice = '';
        }
    }

    /**
     * Get Order id
     * @return mixed
     */
    private function getOrderId()
    {
        return $this->request->getParam('order_id');
    }
}
