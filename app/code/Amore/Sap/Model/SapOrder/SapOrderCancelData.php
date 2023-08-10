<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-25
 * Time: 오후 7:48
 */

namespace Amore\Sap\Model\SapOrder;

use Amore\Sap\Model\Source\Config;
use Eguana\GWLogistics\Model\QuoteCvsLocationRepository;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Api\StoreRepositoryInterface;
use CJ\Middleware\Helper\Data as Helper;

class SapOrderCancelData extends AbstractSapOrder
{
    const CREDITMEMO_SENT_TO_SAP_BEFORE = 0;

    const CREDITMEMO_SENT_TO_SAP_SUCCESS = 1;

    const CREDITMEMO_SENT_TO_SAP_FAIL = 2;

    const CREDITMEMO_RESENT_TO_SAP_SUCCESS = 3;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param Config $config
     * @param Helper $helper
     * @param QuoteCvsLocationRepository $quoteCvsLocationRepository
     * @param AttributeRepositoryInterface $eavAttributeRepositoryInterface
     * @param \Amore\Sap\Logger\Logger $logger
     * @param \CJ\Middleware\Model\Data $orderData
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        StoreRepositoryInterface $storeRepository,
        Config $config,
        Helper $helper,
        QuoteCvsLocationRepository $quoteCvsLocationRepository,
        AttributeRepositoryInterface $eavAttributeRepositoryInterface,
        \Amore\Sap\Logger\Logger $logger,
        \CJ\Middleware\Model\Data $orderData
    ) {
        $this->helper = $helper;
        parent::__construct($searchCriteriaBuilder, $orderRepository,
            $storeRepository, $config,
            $quoteCvsLocationRepository, $eavAttributeRepositoryInterface, $logger, $orderData
        );
    }

    /**
     * @param $incrementId
     * @return array[]
     * @throws NoSuchEntityException
     */
    public function singleOrderData($incrementId)
    {
        /** @var Order $order */
        $order = $this->getOrderInfo($incrementId);
        $source = $this->config->getSourceByStore('store' ,$order->getStoreId());

        $request = [
            "request" => [
                "header" => [
                    "source" => $source
                ],
                "input" => [
                    "itData" => $this->getOrderCancelData($incrementId)
                ]
            ]
        ];
        return $request;
    }

    /**
     * @param $incrementId
     * @param $addressData
     * @return array[]
     * @throws NoSuchEntityException
     */
    public function singleAddressUpdateData($incrementId, $addressData)
    {
        /** @var Order $order */
        $order = $this->getOrderInfo($incrementId);
        $source = $this->config->getSourceByStore('store' ,$order->getStoreId());

        $request = [
            "request" => [
                "header" => [
                    "source" => $source
                ],
                "input" => [
                    "itData" => $this->getOrderAddressUpdate($incrementId, $addressData)
                ]
            ]
        ];
        return $request;
    }

    /**
     * Get order address update
     *
     * @param $incrementId
     * @param $addressData
     * @return array
     * @throws NoSuchEntityException
     */
    public function getOrderAddressUpdate($incrementId, $addressData)
    {
        /** @var Order $orderData */
        $orderData = $this->getOrderInfo($incrementId);
        $storeId = $orderData->getStoreId();

        if ($orderData == null) {
            throw new NoSuchEntityException(
                __("Such order does not exist. Check the data and try again")
            );
        }

        $bindData = [
            "vkorg" => $this->helper->getSalesOrganizationCode('store', $storeId),
            "kunnr" => $this->config->getClient('store', $storeId),
            "odrno" => $orderData->getIncrementId(),
            // 주문 취소 : 1, 주소변경 : 2
            "zchgind" => 2,
            "recvnm" => $addressData->getLastname() . $addressData->getFirstname(),
            "postCode" => $addressData->getPostcode(),
            "addr1" => $addressData->getRegion(),
            "addr2" => $addressData->getCity(),
            "addr3" => preg_replace('/\r\n|\r|\n/',' ',implode(PHP_EOL, $addressData->getStreet())),
            "land1" => $addressData->getCountryId(),
            "telno" => $addressData->getTelephone(),
            "hpno" => $addressData->getTelephone()
        ];

        return $bindData;
    }

    /**
     * Get order cancel data
     *
     * @param $incrementId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getOrderCancelData($incrementId)
    {
        /** @var Order $orderData */
        $orderData = $this->getOrderInfo($incrementId);
        $storeId = $orderData->getStoreId();
        $shippingAddress = $orderData->getShippingAddress();

        if ($orderData == null) {
            throw new NoSuchEntityException(
                __("Such order does not exist. Check the data and try again")
            );
        }

        $bindData = [
            "vkorg" => $this->helper->getSalesOrganizationCode('store', $storeId),
            "kunnr" => $this->config->getClient('store', $storeId),
            "odrno" => $orderData->getIncrementId(),
            // 주문 취소 : 1, 주소변경 : 2
            "zchgind" => 1,
            "recvnm" => $shippingAddress->getName(),
            "postCode" => $shippingAddress->getPostcode(),
            "addr1" => $shippingAddress->getRegion(),
            "addr2" => $shippingAddress->getCity(),
            "addr3" => preg_replace('/\r\n|\r|\n/',' ',implode(PHP_EOL, $shippingAddress->getStreet())),
            "land1" => $shippingAddress->getCountryId(),
            "telno" => $shippingAddress->getTelephone(),
            "hpno" => $shippingAddress->getTelephone()
        ];

        return $bindData;
    }

    /**
     * Get order increment id
     *
     * @param $order Order
     */
    public function getOrderIncrementId($order)
    {
       $orderSendCheck = $order->getData('sap_order_send_check');
       $sapOrderIncrementId = $order->getData('sap_order_increment_id');
       if ($orderSendCheck == null || $orderSendCheck == 1) {
           $incrementId = $order->getIncrementId();
       } else {
           $incrementId = $sapOrderIncrementId;
       }
       return $incrementId;
    }
}
