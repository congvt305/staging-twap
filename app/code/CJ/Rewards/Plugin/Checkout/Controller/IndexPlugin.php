<?php
declare(strict_types=1);

namespace CJ\Rewards\Plugin\Checkout\Controller;

use Amasty\Rewards\Model\Repository\RewardsRepository;
use Amasty\Rewards\Model\RewardsProvider;
use Amore\PointsIntegration\Logger\Logger;
use Amore\PointsIntegration\Model\Config\Source\Actions;
use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Framework\Serialize\Serializer\Json;

class IndexPlugin
{
    const SUCCESS_CODE = '200';
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var RewardsRepository
     */
    private RewardsRepository $rewardsRepository;

    /**
     * @var RewardsProvider
     */
    protected RewardsProvider $rewardsProvider;

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
     * @param \Magento\Customer\Model\Session $_customerSession
     * @param RewardsRepository $rewardsRepository
     * @param RewardsProvider $rewardsProvider
     * @param CustomerPointsSearch $customerPointsSearch
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        \Magento\Customer\Model\Session $_customerSession,
        RewardsRepository $rewardsRepository,
        RewardsProvider $rewardsProvider,
        CustomerPointsSearch $customerPointsSearch,
        Config $config,
        Logger $logger
    ) {
        $this->_customerSession = $_customerSession;
        $this->rewardsRepository = $rewardsRepository;
        $this->rewardsProvider = $rewardsProvider;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function beforeExecute(\Magento\Checkout\Controller\Cart\Index $subject)
    {
        if ($this->_customerSession->isLoggedIn()) {
            $customer = $this->_customerSession->getCustomer();
            $customerPointsInfo = $this->customerPointsSearch->getMemberSearchResult($customer->getId(), $customer->getWebsiteId());
            if ($this->config->getLoggerActiveCheck($customer->getWebsiteId())) {
                $this->logger->info("CUSTOMER POINTS INFO", $customerPointsInfo);
            }

            if ($this->responseValidation($customerPointsInfo) && isset($customerPointsInfo['data']['availablePoint'])) {
                $data = $customerPointsInfo['data'];
//                $data['availablePoint'] = 500000;
                $availablePoint = (int)$data['availablePoint']; //parse to int because if do not have availablePoint, availablePoint = ''
                $customerRewards = $this->rewardsRepository->getCustomerRewardBalance($customer->getId());
                $pointsDiscrepancy = $availablePoint - $customerRewards;
                if (abs($pointsDiscrepancy) > 0) {
                    if ($availablePoint < $customerRewards) {
                        $this->updatePoints((int)$customer->getId(), abs($pointsDiscrepancy), Actions::ACTION_DEDUCT_POINT);
                    }
                    if ($availablePoint > $customerRewards) {
                        $this->updatePoints((int)$customer->getId(), abs($pointsDiscrepancy));
                    }
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

    /**
     * Update points
     *
     * @param int $customerId
     * @param int $points
     * @param string $action
     * @return void
     */
    private function updatePoints($customerId, $points, $action = Actions::ACTION_ADD_POINT)
    {
        $modelRewards = $this->rewardsRepository->getEmptyModel();
        $modelRewards->setCustomerId($customerId);
        $comment = 'Updated from POS';
        $modelRewards->setAmount((float)$points);
        $modelRewards->setComment($comment);
        $modelRewards->setAction(Actions::SYSTEM_REWARDS_SYNC);
        $modelRewards->setVisibleForCustomer(false);
        if ($modelRewards->getAmount() > 0) {
            if ($action == Actions::ACTION_DEDUCT_POINT) {
                $this->rewardsProvider->deductRewardPoints($modelRewards);
            }
            if ($action == Actions::ACTION_ADD_POINT) {
                $this->rewardsProvider->addRewardPoints($modelRewards);
            }
        }
    }
}
