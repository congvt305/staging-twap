<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 11:48
 */

namespace Amore\PointsIntegration\Block\Points;

use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Customer\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Amore\PointsIntegration\Logger\Logger;

class Index extends AbstractPointsBlock
{
    /**
     * @var CustomerPointsSearch
     */
    private $customerPointsSearch;

    /**
     * Index constructor.
     * @param Template\Context $context
     * @param Session $customerSession
     * @param Config $config
     * @param Logger $logger
     * @param Json $json
     * @param \Amore\PointsIntegration\Model\Pagination $pagination
     * @param CustomerPointsSearch $customerPointsSearch
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        Config $config,
        Logger $logger,
        Json $json,
        \Amore\PointsIntegration\Model\Pagination $pagination,
        CustomerPointsSearch $customerPointsSearch,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $config, $logger, $json, $pagination, $data);
        $this->customerPointsSearch = $customerPointsSearch;
    }

    public function getPointsSearchResult()
    {
        $customer = $this->getCustomer();

        $customerPointsInfo = $this->customerPointsSearch->getMemberSearchResult($customer->getId(), $customer->getWebsiteId());
        if ($this->config->getLoggerActiveCheck($customer->getWebsiteId())) {
            $this->logger->info("CUSTOMER POINTS INFO");
            $this->logger->debug($customerPointsInfo);
        }

        if ($this->responseValidation($customerPointsInfo)) {
            return $customerPointsInfo['data'];
        } else {
            return [];
        }
    }

    public function dateFormat($date)
    {
        return date("Y-m-d", strtotime($date));
    }
}
