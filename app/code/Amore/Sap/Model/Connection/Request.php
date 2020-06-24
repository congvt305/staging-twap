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
    const URL_TYPE_DEV = 'dev';

    const URL_TYPE_STG = 'stg';

    const URL_TYPE_PRD = 'prd';

    const URL_TYPE_SELECTED = 'sap/general/url_select';

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

    public function postRequest($requestData, $type = 0)
    {
        $path = $this

        $url = $this->getUrl();

        $this->curl->addHeader('Content-Type', 'application/json');
        $this->curl->post($url, $requestData);

        $response = $this->curl->getBody();
        $result = $this->json->unserialize($response);

        if ($result['code'] == '0000') {
            return $result['message'];
        } else {
            return $result['data'];
        }
    }

    public function getUrl()
    {
        $urlType = $this->scopeConfig->getValue(self::URL_TYPE_SELECTED,'store');
        switch ($urlType) {
            case self::URL_TYPE_DEV:
                $url = $this->scopeConfig->getValue(self::URL_TYPE_DEV, 'store');
                break;
            case self::URL_TYPE_STG:
                $url = $this->scopeConfig->getValue(self::URL_TYPE_STG, 'store');
                break;
            case self::URL_TYPE_PRD:
                $url = $this->scopeConfig->getValue(self::URL_TYPE_PRD, 'store');
                break;
            default:
                $url = $this->scopeConfig->getValue(self::URL_TYPE_DEV, 'store');
        }
        return $url;
    }

    public function getPath($type)
    {
        switch ($type) {
            case 0:
                $path = $this->scopeConfig->getValue(self::ORDER_CONFIRM_PATH, 'store');
                break;
            case 1:
                $path = $this->scopeConfig->getValue(self::ORDER_CANCEL_PATH, 'store');
                break;
            default:
                $path = $this->scopeConfig->getValue(self::ORDER_CONFIRM_PATH, 'store');
        }
        return $path;
    }
}
