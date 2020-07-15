<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/20/20
 * Time: 6:08 PM
 */

namespace Eguana\GWLogistics\Model\Request;


class QueryLogisticsInfo
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Eguana\GWLogistics\Model\Lib\EcpayLogistics
     */
    private $ecpayLogistics;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    private $dateTimeFactory;
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $helper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Eguana\GWLogistics\Model\Lib\EcpayLogistics $ecpayLogistics,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Eguana\GWLogistics\Helper\Data $helper
    ) {
        $this->logger = $logger;
        $this->ecpayLogistics = $ecpayLogistics;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->helper = $helper;
    }

    /**
     * @param $allPayLogisticsID
     * @return array
     */
    public function sendRequest($allPayLogisticsID)
    {
        try {
            $this->ecpayLogistics->HashKey = '5294y06JbISpM5x9';
            $this->ecpayLogistics->HashIV = 'v77hoKGq4kWxNNIS';
            $this->ecpayLogistics->Send = [
                'MerchantID' => '2000132',
                'AllPayLogisticsID' => $allPayLogisticsID, // save this in order!
                'PlatformID' => ''
            ];
            $result = $this->ecpayLogistics->QueryLogisticsInfo();
            $this->logger->debug('GWL query logistics(track) result: ', $result);
            return $result;
        } catch (\Exception $e) {
            $this->logger->critical('GWL query logistics(track) failed');
            $this->logger->critical($e->getMessage());
        }

    }

}
