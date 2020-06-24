<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-03
 * Time: 오후 5:18
 */

namespace Amore\Sap\Model\Connection;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Request
{
    const URL_ACTIVE = 'sap/general/active';

    const URL_REQUEST = 'sap/general/url';

    const ORDER_CONFIRM_PATH = 'sap/url_path/order_confirm_path';

    const ORDER_CANCEL_PATH = 'sap/url_path/order_cancel_path';

    /**
     * @var Curl
     */
    private $curl;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor.
     *
     * @param Curl $curl
     * @param Json $json
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Curl $curl,
        Json $json,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->scopeConfig = $scopeConfig;
    }

    public function postRequest($requestData, $storeId, $type = 'confirm')
    {
        $url = $this->getUrl($storeId);
        $path = $this->getPath($storeId, $type);

        if (empty($url) || empty($path)) {
            return ['code' => "0001", "Url or Path field is empty. Please Check configuration"];
        } else {
            $url = $this->getUrl($storeId) . $path;

            $this->curl->addHeader('Content-Type', 'application/json');
            $this->curl->post($url, $requestData);

            $response = $this->curl->getBody();

            $result = $this->json->unserialize($response);
//        $result = ["code" => "0000", "message" => "success test"];
//        $result = ["code" => "0001", "message" => "fail test"];

            if ($result['code'] == '0000') {
                return $result;
            } else {
                return $result;
            }
        }
    }

    public function getUrl($storeId)
    {
        $url = '';

        $activeCheck = $this->scopeConfig->getValue(self::URL_ACTIVE, 'store', $storeId);

        if ($activeCheck) {
            $url = $this->scopeConfig->getValue(self::URL_REQUEST, 'store', $storeId);
        }

        return $url;
    }

    public function getPath($storeId, $type)
    {
        switch ($type) {
            case 'confirm':
                $path = $this->scopeConfig->getValue(self::ORDER_CONFIRM_PATH, 'store', $storeId);
                break;
            case 'cancel':
                $path = $this->scopeConfig->getValue(self::ORDER_CANCEL_PATH, 'store', $storeId);
                break;
            default:
                $path = $this->scopeConfig->getValue(self::ORDER_CONFIRM_PATH, 'store', $storeId);
        }
        return $path;
    }
}
