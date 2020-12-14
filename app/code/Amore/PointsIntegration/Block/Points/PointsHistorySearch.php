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
     * PointsHistorySearch constructor.
     * @param Template\Context $context
     * @param Session $customerSession
     * @param Config $config
     * @param Logger $logger
     * @param Json $json
     * @param Pagination $pagination
     * @param \Amore\PointsIntegration\Model\PointsHistorySearch $pointsHistorySearch
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
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $config, $logger, $json, $pagination, $data);
        $this->pointsHistorySearch = $pointsHistorySearch;
    }

    public function getPointsHistoryResult()
    {
        $customer = $this->getCustomer();

        $pointsHistoryResult = $this->pointsHistorySearch->getPointsHistoryResult($customer->getId(), $customer->getWebsiteId(), 1);

        if ($this->config->getLoggerActiveCheck($customer->getWebsiteId())) {
            $this->logger->info("REDEMPTION POINTS INFO");
            $this->logger->debug($pointsHistoryResult);
        }

        if ($this->responseValidation($pointsHistoryResult)) {
            return $this->pagination->ajaxPagination($pointsHistoryResult['data']['point_data']);
        } else {
            return [];
        }
    }
}
