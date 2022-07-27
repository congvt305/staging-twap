<?php

namespace CJ\VLogicOrder\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class VLogicShipmentActivityResponse extends Column
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
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
        array                    $components = [],
        array                    $data = []
    ){
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->orderRepository = $orderRepository;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $order = $this->orderRepository->get($item["entity_id"]);
                if ($order) {
                    $item[$this->getData('name')] = !empty($order->getData("receive_track_number_vlogic")) ? 'Successful' : 'None';
                }
            }
        }
        return $dataSource;
    }
}
