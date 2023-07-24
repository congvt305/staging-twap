<?php

namespace CJ\Rewards\Model;

use Amasty\Rewards\Model\Repository\RewardsRepository;
use Amore\PointsIntegration\Logger\Logger;
use Amore\PointsIntegration\Model\Config\Source\Actions;
use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Amore\PointsIntegration\Model\PointUpdate;
use Amore\PointsIntegration\Model\Source\Config;

class ReCheckAndUpdatePoint
{

    const SUCCESS_CODE = '200';

    /**
     * @var RewardsRepository
     */
    private  $rewardsRepository;
    /**
     * @var CustomerPointsSearch
     */
    private $customerPointsSearch;

    /**
     * @var Config
     */
    protected $config;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var PointUpdate
     */
    private $pointUpdate;

    /**
     * @param RewardsRepository $rewardsRepository
     * @param CustomerPointsSearch $customerPointsSearch
     * @param Config $config
     * @param Logger $logger
     * @param PointUpdate $pointUpdate
     */
    public function __construct(
        RewardsRepository $rewardsRepository,
        CustomerPointsSearch $customerPointsSearch,
        Config $config,
        Logger $logger,
        PointUpdate $pointUpdate,
    ) {
        $this->rewardsRepository = $rewardsRepository;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->config = $config;
        $this->logger = $logger;
        $this->pointUpdate = $pointUpdate;
    }

    /**
     * Recheck and update point
     *
     * @param $customer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function update($customer) {
        $customerPointsInfo = $this->customerPointsSearch->getMemberSearchResult($customer->getId(), $customer->getWebsiteId());
        if ($this->config->getLoggerActiveCheck($customer->getWebsiteId())) {
            $this->logger->info("CUSTOMER POINTS INFO", $customerPointsInfo);
        }

        if ($this->responseValidation($customerPointsInfo) && isset($customerPointsInfo['data']['availablePoint'])) {
            $data = $customerPointsInfo['data'];
            //$data['availablePoint'] = 500000;
            $availablePoint = (int)$data['availablePoint']; //parse to int because if do not have availablePoint, availablePoint = ''
            $customerRewards = $this->rewardsRepository->getCustomerRewardBalance($customer->getId());
            if (!$this->pointUpdate->isNeedUpdatePointFromPos($customer->getId())) {
                return;
            }
            $pointsDiscrepancy = $availablePoint - $customerRewards;
            if (abs($pointsDiscrepancy) > 0) {
                if ($availablePoint < $customerRewards) {
                    if ($availablePoint < 0) {
                        $pointsDiscrepancy = $customerRewards;
                    }
                    $this->pointUpdate->updatePoints((int)$customer->getId(), abs($pointsDiscrepancy), Actions::ACTION_DEDUCT_POINT);
                }
                if ($availablePoint > $customerRewards) {
                    $this->pointUpdate->updatePoints((int)$customer->getId(), abs($pointsDiscrepancy));
                }
            }
        }
    }

    /**
     * Response validation
     *
     * @param array $response
     * @return bool
     */
    private function responseValidation($response): bool
    {
        $data = $response['data'] ?? null;

        if (!$data) {
            return false;
        }
        $message = $data['statusCode'] ?? null;
        if ($message && $message == self::SUCCESS_CODE) {
            return true;
        }
        return false;
    }
}
