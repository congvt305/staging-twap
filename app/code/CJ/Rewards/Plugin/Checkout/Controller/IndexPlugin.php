<?php
declare(strict_types=1);

namespace CJ\Rewards\Plugin\Checkout\Controller;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Model\Repository\RewardsRepository;
use Amasty\Rewards\Model\RewardsProvider;
use Amore\PointsIntegration\Logger\Logger;
use Amore\PointsIntegration\Model\Config\Source\Actions;
use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Framework\DB\Select;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

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
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @param \Magento\Customer\Model\Session $_customerSession
     * @param RewardsRepository $rewardsRepository
     * @param RewardsProvider $rewardsProvider
     * @param CustomerPointsSearch $customerPointsSearch
     * @param Config $config
     * @param Logger $logger
     * @param CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Magento\Customer\Model\Session $_customerSession,
        RewardsRepository $rewardsRepository,
        RewardsProvider $rewardsProvider,
        CustomerPointsSearch $customerPointsSearch,
        Config $config,
        Logger $logger,
        CollectionFactory $orderCollectionFactory
    ) {
        $this->_customerSession = $_customerSession;
        $this->rewardsRepository = $rewardsRepository;
        $this->rewardsProvider = $rewardsProvider;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->config = $config;
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
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
                $orderCollection = $this->orderCollectionFactory->create();
                $orderCollection->getSelect()->reset(Select::COLUMNS)->columns(EntityInterface::POINTS_SPENT);
                $orderCollection->addFieldToFilter('customer_id', $customer->getId())
                    ->addFieldToFilter('pos_order_paid_sent', 0)
                    ->addFieldToFilter(EntityInterface::POINTS_SPENT, ['neq' => 'NULL']);
                if ($orderCollection->count()) {
                    return;
                }
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
