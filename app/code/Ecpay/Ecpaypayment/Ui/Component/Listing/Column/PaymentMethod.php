<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/07/22
 * Time: 1:03 PM
 */

namespace Ecpay\Ecpaypayment\Ui\Component\Listing\Column;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Api\SearchCriteriaBuilder;

class PaymentMethod extends Column
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Ecpay\Ecpaypayment\Model\Config\Source\PaymentMethods
     */
    private $paymentMethods;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Ecpay\Ecpaypayment\Model\Config\Source\PaymentMethods $paymentMethods,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->paymentMethods = $paymentMethods;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $optionArray = $this->paymentMethods->toOptionArray();

            foreach ($dataSource['data']['items'] as & $item) {
                $order = $this->orderRepository->get($item['entity_id']);

                foreach ($optionArray as $value) {
                    if ($value['value'] == $order->getData('ecpay_payment_method')) {
                        $item[$this->getData('name')] = $value['label'];
                    }
                }
            }
        }
        return $dataSource;
    }
}
