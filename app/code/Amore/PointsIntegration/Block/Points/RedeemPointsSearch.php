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
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Pager;

class RedeemPointsSearch extends AbstractPointsBlock
{
    /**
     * @var \Amore\PointsIntegration\Model\RedeemPointsSearch
     */
    private $redeemPointsSearch;

    /**
     * RedeemPointsSearch constructor.
     * @param Template\Context $context
     * @param Session $customerSession
     * @param Config $config
     * @param Logger $logger
     * @param Json $json
     * @param \Amore\PointsIntegration\Model\Pagination $pagination
     * @param \Amore\PointsIntegration\Model\RedeemPointsSearch $redeemPointsSearch
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        Config $config,
        Logger $logger,
        Json $json,
        \Amore\PointsIntegration\Model\Pagination $pagination,
        \Amore\PointsIntegration\Model\RedeemPointsSearch $redeemPointsSearch,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $config, $logger, $json, $pagination, $data);
        $this->redeemPointsSearch = $redeemPointsSearch;
    }

    public function getPointsRedeemSearchResult()
    {
        $customer = $this->getCustomer();

        $redeemPointsResult = $this->redeemPointsSearch->getRedeemSearchResult($customer->getId(), $customer->getWebsiteId(), 1);

        if ($this->config->getLoggerActiveCheck($customer->getWebsiteId())) {
            $this->logger->info("REDEMPTION POINTS INFO");
            $this->logger->debug($redeemPointsResult);
        }

        if ($this->responseValidation($redeemPointsResult)) {
            return $this->pagination->ajaxPagination($redeemPointsResult['data']['redemption_data']);
        } else {
            return [];
        }
    }
}
