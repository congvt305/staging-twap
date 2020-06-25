<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-23
 * Time: 오후 6:39
 */
namespace Amore\Sap\Plugin\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Ui\Component\Listing\Column\ViewAction;

class SendActionPlugin
{
    /**
     * @var ContextInterface
     */
    private $context;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->context = $context;
        $this->urlBuilder = $urlBuilder;
        $this->orderRepository = $orderRepository;
    }

    public function afterPrepareDataSource(ViewAction $subject, $result)
    {
        if (isset($result['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');
            foreach ($result['data']['items'] as &$item) {
                $order = $this->orderRepository->get($item['entity_id']);
                if ($order->getStatus() == 'processing') {
                    $item[$subject->getData('name')]['send'] = [
                        'href' => $this->urlBuilder->getUrl('sap/saporder/ordersend', ['id' => $item['entity_id']]),
                        'label' => __('Send'),
                        'confirm' => [
                            'title' => __('Send Order "${ $.$data.increment_id }" to SAP'),
                            'message' => __('Are you sure you wan\'t to send a "${ $.$data.increment_id }" order?')
                        ]
                    ];
                }
            }
        }
        return $result;
    }
}
