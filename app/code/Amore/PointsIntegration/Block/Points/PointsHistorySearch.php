<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-07
 * Time: 오전 9:58
 */

namespace Amore\PointsIntegration\Block\Points;

use Amore\PointsIntegration\Logger\Logger;
use Amore\PointsIntegration\Model\Pagination;
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Customer\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;

class PointsHistorySearch extends AbstractPointsBlock
{
    /**
     * @var \Amore\PointsIntegration\Model\PointsHistorySearch
     */
    private $pointsHistorySearch;
    /**
     * @var \Amore\PointsIntegration\Model\CustomerPointsSearch
     */
    private $customerPointsSearch;

    /**
     * PointsHistorySearch constructor.
     * @param Template\Context $context
     * @param Session $customerSession
     * @param Config $config
     * @param Logger $logger
     * @param Json $json
     * @param Pagination $pagination
     * @param \Amore\PointsIntegration\Model\PointsHistorySearch $pointsHistorySearch
     * @param \Amore\PointsIntegration\Model\CustomerPointsSearch $customerPointsSearch
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        Config $config,
        Logger $logger,
        Json $json,
        \Amore\PointsIntegration\Model\Pagination $pagination,
        \Amore\PointsIntegration\Model\PointsHistorySearch $pointsHistorySearch,
        \Amore\PointsIntegration\Model\CustomerPointsSearch $customerPointsSearch,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $config, $logger, $json, $pagination, $data);
        $this->pointsHistorySearch = $pointsHistorySearch;
        $this->customerPointsSearch = $customerPointsSearch;
    }

    public function getPointsHistoryResult()
    {
        $customer = $this->getCustomer();

        $pointsHistoryResult = $this->pointsHistorySearch->getPointsHistoryResult($customer->getId(), $customer->getWebsiteId(), 1);

        if ($this->config->getLoggerActiveCheck($customer->getWebsiteId())) {
            $this->logger->info("POINTS HISTORY INFO");
            $this->logger->debug($pointsHistoryResult);
        }

        if ($this->responseValidation($pointsHistoryResult)) {
            $pointsData = $pointsHistoryResult['data']['point_data'];
            return $this->pagination->ajaxPagination($pointsData);
        } else {
            return [];
        }
    }

    public function getCustomerPointsResulst()
    {
        $customer = $this->getCustomer();
        $customerPointsResult = $this->customerPointsSearch->getMemberSearchResult($customer->getId(), $customer->getWebsiteId());

        if ($this->responseValidation($customerPointsResult)) {
            return $customerPointsResult['data'];
        } else {
            return [];
        }
    }
}
