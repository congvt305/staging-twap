<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/27/20
 * Time: 3:47 PM
 */

namespace Eguana\StoreSms\Model;


class SmsManagement implements \Eguana\StoreSms\Api\SmsManagementInterface
{
    /**
     * @var \Eguana\StoreSms\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curl;

    public function __construct(
        \Eguana\StoreSms\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->curl = $curl;
    }

    /**
     * @param string $number
     * @param string $message
     * @param int|null $storeId
     * @return bool
     */
    public function sendMessage(string $number, string $message, int $storeId = null): bool
    {
        $result = true;
        try {
            if ($storeId == null) {
                $storeId = $this->storeManager->getStore()->getId();
            }

            $apiUserName = $this->helper->getApiCredentials('api_login', $storeId);
            $apiPassword = $this->helper->getApiCredentials('api_password', $storeId);
            $sender = $this->helper->getSender($storeId);
            $apiUrl = $this->helper->getApiCredentials('api_url', $storeId);
            $authorizationKey = "Basic " . base64_encode($apiUserName . ":" . $apiPassword);
            $header = [
                "accept: application/json",
                "authorization: " . $authorizationKey,
                "content-type: application/json"
            ];
            $param = [
                'from' => $sender,
                'to' => $number,
                'text' => $message
            ];
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->curl->setOption(CURLOPT_HTTPHEADER, $header);
            $this->curl->setOption(CURLOPT_HEADER, false);
            $this->curl->setOption(CURLOPT_POST, true);
            $this->curl->setOption(CURLOPT_POSTFIELDS, json_encode($param));
            $this->curl->post($apiUrl, $param);
            $status = $this->curl->getStatus();

            if ($status != 200) {
                $result = false;
            }

        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }
}
