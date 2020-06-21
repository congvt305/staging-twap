<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/19/20
 * Time: 7:00 AM
 */

namespace Eguana\GWLogistics\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Psr\Log\LoggerInterface;

class GetSelectedCvsData extends Action implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;
    /**
     * @var RawFactory
     */
    private $rawFactory;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Controller\Result\RawFactory $rawFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        Context $context
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->rawFactory = $rawFactory;
    }

    public function execute()
    {
        $this->logger->debug(__METHOD__);
        $httpBadRequestCode = 400;

        $response = [
            'errors' => true,
            'message' => __('Merchant Trade No is required.'),
        ];

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->rawFactory->create();
        $quoteData = $this->serializer->unserialize($this->getRequest()->getContent());

        if (!$quoteData || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        try {
            $response = $this->getSelectedStoreData($quoteData);
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage(),
            ];
        }
        return $this->jsonFactory->create()->setData($response);
    }
    private function getSelectedStoreData(array $quoteData)
    {
        /*
         * cvsStoreData:  {
        "MerchantID":"2000132",
        "MerchantTradeNo":"1592515364346",
        "LogisticsSubType":"UNIMART",
        "CVSStoreID":"991182",
        "CVSStoreName":"馥樺門市",
        "CVSAddress":"台北市南港區三重路23號1樓",
        "CVSTelephone":"",
        "CVSOutSide":"0",
        "ExtraData":""
        }
        */
        $storeData = [
            'MerchantTradeNo' => '1592515364346',
            'LogisticsSubType' => 'UNIMART',
            'CVSStoreID' => '991182',
            'CVSStoreName' => '馥樺門市',
            'CVSAddress' => '台北市南港區三重路23號1樓',
            'CVSTelephone' => '',
            'CVSOutSide' => '',
            'ExtraData' => '',
        ];
        return $storeData;
    }

}
