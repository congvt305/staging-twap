<?php

namespace CJ\VLogicOrder\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use CJ\VLogicOrder\Helper\Data as VLogicHelper;
use Magento\Sales\Api\OrderRepositoryInterface;

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
     * @var VLogicHelper
     */
    private $vlogicHelper;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param Action\Context $context
     * @param JsonFactory $jsonFactory
     * @param Curl $curl
     * @param Json $json
     * @param VLogicHelper $vlogicHelper
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        Curl $curl,
        Json $json,
        VLogicHelper $vlogicHelper,
        OrderRepositoryInterface             $orderRepository
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->curl = $curl;
        $this->json = $json;
        $this->vlogicHelper = $vlogicHelper;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->jsonFactory->create();
            $url = $this->vlogicHelper->getVLogicHost();
            $postData = [
                "StorerCode" => $this->vlogicHelper->getVLogicStorerCode(),
                "Username" => $this->vlogicHelper->getVLogicUsername(),
                "Password" => $this->vlogicHelper->getVLogicCPassword()
            ];
            $this->curl->addHeader('Content-Type', 'application/json');
            $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
            $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
            $this->curl->post($url, $this->json->serialize($postData));
            $response = $this->curl->getBody();
            $response = $this->json->unserialize($response);

            if (isset($response['Token']) && $response['Token']) {
                return $result->setData([
                    'success' => true,
                    'url' => $url,
                    'data' => $response,
                ]);
            } else {
                return $result->setData([
                    'success' => false,
                    'url' => $url,
                    'message' => __('Cannot get access token from VLogic'),
                ]);
            }
        } catch (\Exception $exception) {
            return $this->jsonFactory->create()->setData(['success' => false]);
        }
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return parent::_isAllowed();
    }
}
