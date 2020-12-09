<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 9:50
 */

namespace Amore\PointsIntegration\Block\Points;

use Amore\PointsIntegration\Logger\Logger;
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;

abstract class AbstractPointsBlock extends Template
{
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var Json
     */
    private $json;

    /**
     * Index constructor.
     * @param Template\Context $context
     * @param Session $customerSession
     * @param Config $config
     * @param Logger $logger
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        Config $config,
        Logger $logger,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->config = $config;
        $this->logger = $logger;
        $this->json = $json;
    }

    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    public function responseValidation($response)
    {
        if (isset($response['statusCode']) && $response['statusCode'] == '200') {
            return true;
        } else {
            return false;
        }
    }
}
