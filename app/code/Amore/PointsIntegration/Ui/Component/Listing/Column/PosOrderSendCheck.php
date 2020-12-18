<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-18
 * Time: 오전 10:02
 */

namespace Amore\PointsIntegration\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class PosOrderSendCheck extends Column
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * PosOrderSendCheck constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->orderRepository = $orderRepository;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $order  = $this->orderRepository->get($item["entity_id"]);
                $item[$this->getData('name')] = $this->getLabelForPosSendStatus($order->getData("pos_order_send_check"));
            }
        }
        return $dataSource;
    }

    public function getLabelForPosSendStatus($value)
    {
        switch ($value) {
            case 0:
                $label = "Not yet sent or Failed";
                break;
            case 1:
                $label = "Success";
                break;
            default:
                $label = "Not yet sent or Failed";
        }
        return $label;
    }
}
