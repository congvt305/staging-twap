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
     * SapOrderSend constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
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
            array $components = [],
            array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . sprintf('/var/log/column2_%s.log',date('Ymd')));
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);
//        $logger->info($dataSource);

        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');
            foreach ($dataSource['data']['items'] as $item) {
                $order = $this->orderRepository->get($item['entity_id']);
                $incrementId = $order->getIncrementId();
                $name = $this->getData('name');
                $url = $this->urlBuilder->getUrl(
                    static::URL_PATH_SEND_ORDER_TO_SAP,
                    ['increment_id' => $incrementId, 'store' => $storeId]
                );
//                $item['aaa'] = 1;
//                $item[$name] = $this->getLinkHtml($url, $this->sapOrderSendButton());
//                $item[$name] = html_entity_decode("<a href='{$url}'><button>Send Order</button></a>");

                $item[$name . '_html'] = "<button class='button'><span>Send Mail</span></button>";
                $item[$name . '_title'] = __('Please enter a message that you want to send to customer');
                $item[$name . '_submitlabel'] = __('Send');
                $item[$name . '_cancellabel'] = __('Reset');
                $item[$name . '_orderid'] = $item['entity_id'];

                $item[$name . '_formaction'] = $this->urlBuilder->getUrl('grid/customer/sendmail');

                $logger->info($item[$name]);
            }
        }
        $logger->info($dataSource);
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
