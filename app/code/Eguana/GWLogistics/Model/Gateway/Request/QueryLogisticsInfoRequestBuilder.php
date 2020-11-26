<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/12/20
 * Time: 9:07 AM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Request;

class QueryLogisticsInfoRequestBuilder implements \Eguana\GWLogistics\Model\Gateway\Request\RequestBuilderInterface
{
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $helper;

    /**
     * QueryLogisticsInfoRequestBuilder constructor.
     * @param \Eguana\GWLogistics\Helper\Data $helper
     */
    public function __construct(\Eguana\GWLogistics\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    public function build(array $buildSubject): ?array
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $buildSubject['order'];
        $createShipmentResponse = $buildSubject['createShipmentResponse'];
        $data = [
            'HashKey' => $this->helper->getHashKey($order->getStoreId()),
            'HashIV' => $this->helper->getHashIv($order->getStoreId()),
            'Params' => [
                'MerchantID' => $this->helper->getMerchantId($order->getStoreId()),
                'AllPayLogisticsID' => $createShipmentResponse['AllPayLogisticsID'], // save this in order!
                'PlatformID' => $this->helper->getPlatformId($order->getStoreId()) ?? ''
            ],
        ];
        return $data;
    }
}
