<?php

namespace Amore\PointsIntegration\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Magento\Customer\Model\CustomerFactory;

class PosGrade extends Column
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CustomerPointsSearch
     */
    private $customerPointsSearch;

    /**
     * @var CustomerFactory
     */
    private $customer;

    /**
     * SapResponse constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ContextInterface         $context,
        UiComponentFactory       $uiComponentFactory,
        CustomerPointsSearch     $customerPointsSearch,
        CustomerFactory          $customer,
        array                    $components = [],
        array                    $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->orderRepository = $orderRepository;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->customer = $customer;
    }

    /**
     * assign data for pos_customer_grade sales_order_gird.xml
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $order = $this->orderRepository->get($item["entity_id"]);
                $item[$this->getData('name')] = $order->getData("pos_customer_grade");
            }
        }
        return $dataSource;
    }

}
