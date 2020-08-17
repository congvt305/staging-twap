<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-08
 * Time: 오후 1:20
 */

namespace Amore\Sap\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Escaper;
use Amore\Sap\Model\Source\Config;

class SapOrderSend extends Column
{
    const URL_PATH_SEND_ORDER_TO_SAP = 'sap/order/send';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var Config
     */
    private $config;

    /**
     * SapOrderSend constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param Config $config
     * @param array $components
     * @param array $data
     */
    public function __construct(
            ContextInterface $context,
            UiComponentFactory $uiComponentFactory,
            OrderRepositoryInterface $orderRepository,
            SearchCriteriaBuilder $searchCriteriaBuilder,
            UrlInterface $urlBuilder,
            Escaper $escaper,
            Config $config,
            array $components = [],
            array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        $this->config = $config;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $viewUrlPath = $this->getData('config/viewUrlPath') ?: '#';
                    $storeId = $this->orderRepository->get($item['entity_id'])->getStoreId();
                    $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'entity_id';
                    if (($item['status'] == 'processing' || $item['status'] == 'sap_fail' || $item['status'] == 'processing_with_shipment')
                        && $this->config->getActiveCheck('store', $storeId)) {
                        $item[$this->getData('name')] = [
                            'view' => [
                                'href' => $this->urlBuilder->getUrl(
                                    $viewUrlPath,
                                    [
                                        $urlEntityParamName => $item['entity_id']
                                    ]
                                ),
                                'label' => __('View')
                            ],
                            'send' => [
                                'href' => $this->urlBuilder->getUrl(
                                    "sap/saporder/ordersend",
                                    [
                                        $urlEntityParamName => $item['entity_id']
                                    ]
                                ),
                                'label' => __('Send'),
                                'confirm' => [
                                    'title' => __('Send Order "${ $.$data.increment_id }" to SAP'),
                                    'message' => __('Are you sure you wan\'t to send a "${ $.$data.increment_id }" order?')
                                ]
                            ]
                        ];
                    } else {
                        $item[$this->getData('name')] = [
                            'view' => [
                                'href' => $this->urlBuilder->getUrl(
                                    $viewUrlPath,
                                    [
                                        $urlEntityParamName => $item['entity_id']
                                    ]
                                ),
                                'label' => __('View')
                            ]
                        ];
                    }
                }
            }
        }
        return $dataSource;
    }

    /**
     * @return string
     */
    private function sapOrderSendButton()
    {
        return '<button class="button action primary sap-order-send-btn">' . $this->escaper->escapeHtml(
                __('Send to SAP')
            ) . '</button>';
    }

    /**
     * @param string $link
     * @param string $button
     *
     * @return string
     */
    private function getLinkHtml($link, $button)
    {
        return sprintf(
            '<a class="sap-order-send-btn-text" target="_blank" href="%s">%s</a>',
            $this->escaper->escapeUrl($link),
            $button
        );
    }
}
