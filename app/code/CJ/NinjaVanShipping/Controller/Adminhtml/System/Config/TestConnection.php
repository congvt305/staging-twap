<?php

namespace CJ\NinjaVanShipping\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use CJ\NinjaVanShipping\Helper\Data as NinjaVanHelper;

class TestConnection extends Action
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var NinjaVanHelper
     */
    private $ninjavanHelper;

    /**
     * OrderStatusCheck constructor.
     * @param Action\Context $context
     * @param JsonFactory $jsonFactory
     * @param Curl $curl
     * @param Json $json
     * @param NinjaVanHelper $ninjavanHelper
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        Curl $curl,
        Json $json,
        NinjaVanHelper $ninjavanHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->curl = $curl;
        $this->json = $json;
        $this->ninjavanHelper = $ninjavanHelper;
    }

    public function execute()
    {
        try {
            // https://api-sandbox.ninjavan.co/{countryCode}/2.0/oauth/access_token
            // https://api.ninjavan.co/{countryCode}/2.0/oauth/access_token
            $result = $this->jsonFactory->create();
            $country = 'my';
            $uri = $country.'/2.0/oauth/access_token';
            $url = $this->ninjavanHelper->getNinjaVanHost() . $uri;
            $postData = [
                "client_id" => $this->ninjavanHelper->getNinjaVanClientId(),
                "client_secret" => $this->ninjavanHelper->getNinjaVanClientKey(),
                "grant_type" => "client_credentials"
            ];
            $this->curl->addHeader('Content-Type', 'application/json');
            $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
            $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
            $this->curl->post($url, $this->json->serialize($postData));
            $response = $this->curl->getBody();
            $response = $this->json->unserialize($response);
            if (isset($response['access_token']) && $response['access_token']) {
                return $result->setData([
                    'success' => true,
                    'url' => $url,
                    'data' => $response,
                ]);
            } else {
                return $result->setData([
                    'success' => false,
                    'url' => $url,
                    'message' => __('Cannot get access token from NinjaVan'),
                ]);
            }
        } catch (\Exception $exception) {
            return $this->jsonFactory->create()->setData(['success' => false]);
        }
    }

    public function _isAllowed()
    {
        return parent::_isAllowed();
    }
}
