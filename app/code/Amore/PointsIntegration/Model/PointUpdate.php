<?php
declare(strict_types=1);

namespace Amore\PointsIntegration\Model;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Model\RewardsProvider;
use Amore\PointsIntegration\Model\Source\Config;
use CJ\Middleware\Helper\Data as MiddlewareHelper;
use CJ\Middleware\Model\PosRequest;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Sales\Model\Order as MagentoOrder;
use Exception;
use Amasty\Rewards\Model\Repository\RewardsRepository;
use Amore\PointsIntegration\Model\Config\Source\Actions;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Psr\Log\LoggerInterface as Logger;

class PointUpdate extends PosRequest
{
    const POINT_EARN = 'EARN';
    const POINT_REDEEM = 'REDEEM';
    const POINT_REASON_PURCHASE = '000010';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * @var RewardsRepository
     */
    protected $rewardsRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var RewardsProvider
     */
    private $rewardsProvider;

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @param Curl $curl
     * @param MiddlewareHelper $middlewareHelper
     * @param Logger $logger
     * @param Config $config
     * @param RewardsRepository $rewardsRepository
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param OrderRepository $orderRepository
     * @param RewardsProvider $rewardsProvider
     * @param CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        Curl $curl,
        MiddlewareHelper $middlewareHelper,
        Logger $logger,
        Config $config,
        RewardsRepository $rewardsRepository,
        CustomerRepositoryInterface $customerRepositoryInterface,
        OrderRepository $orderRepository,
        RewardsProvider $rewardsProvider,
        CollectionFactory $orderCollectionFactory
    ) {
        $this->rewardsRepository = $rewardsRepository;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->orderRepository = $orderRepository;
        $this->rewardsProvider = $rewardsProvider;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($curl, $middlewareHelper, $logger, $config);
    }

    /**
     * @param MagentoOrder $order
     * @param string $pointType
     * @param string $useReason
     * @param $pointAmount
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function pointUpdate(
        MagentoOrder $order,
        $pointAmount,
        string $pointType = self::POINT_EARN,
        string $useReason = self::POINT_REASON_PURCHASE
    ) {
        $dash = '====================';
        $this->logger->info($dash);

        $customer = $this->customerRepositoryInterface->getById($order->getCustomerId());
        $integrationNumberAttr = $customer->getCustomAttribute('integration_number');
        $integrationNumber = $integrationNumberAttr ? $integrationNumberAttr->getValue() : '';

        $orderId = $order->getIncrementId();
        if ($pointType == self::POINT_EARN) {
            $orderId = 'R'.$order->getIncrementId();
        }
        $dataRequest = [
            'cstmIntgSeq' => $integrationNumber,
            'type' => $pointType,
            'useRsnCd' => $useReason,
            'pointAmount' => $pointAmount,
            'orderID' => $orderId
        ];
        $this->logger->info('POINT UPDATE REQUEST', $dataRequest);
        try {
            if ($pointType == self::POINT_REDEEM) {
                $order->setData('pos_order_use_point_resend', true);
            } else {
                $order->setData('pos_order_return_point_resend', true);
            }
            $dataResponse = $this->sendRequest(
                $dataRequest,
                $order->getStore()->getWebsiteId(),
                'pointUpdate'
            );

            $data = $this->handleResponse($dataResponse, 'pointUpdate');
//            $data['availablePoint'] = 1000000;
            $this->logger->info('POINT UPDATE RESPONSE', $dataResponse);
            $message = 'Something when wrong while updating point.';
            if ($data) {
                $messageResponse = $data['statusCode'] ?? null;
                // Check if have not been sent to POS yet so update point
                if ($messageResponse == 'S') {
                    $customerRewards = $this->rewardsRepository->getCustomerRewardBalance($order->getCustomerId());
                    $pointUpdate = $data['availablePoint'] - $customerRewards;
                    $type = Actions::ACTION_ADD_POINT;
                    if ($pointUpdate < 0) {
                        $type = Actions::ACTION_DEDUCT_POINT;
                    }
                    if ($pointUpdate != 0) {
                        $this->updatePoints($order->getCustomerId(), abs($pointUpdate), $type);
                    }
                }

                if ($pointType == self::POINT_REDEEM) {
                    $order->setData('pos_order_use_point_resend', false);
                } else {
                    $order->setData('pos_order_return_point_resend', false);
                }
                $this->orderRepository->save($order);
                $message = null;
            }
            if ($message) {
                $this->logger->error('MESSAGE: ' . $message);
                $this->logger->info($dash);
                $this->orderRepository->save($order);
                throw new Exception($message);
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->info($dash);
            $this->orderRepository->save($order);
            throw new Exception('Something when wrong while updating point.');
        }
        $this->logger->info($dash);

    }

    /**
     * Set data and update point to DB
     *
     * @param $customerId
     * @param $points
     * @param $action
     * @param $comment
     * @return void
     */
    public function updatePoints($customerId, $points, $action = Actions::ACTION_ADD_POINT, $comment = 'Updated from POS')
    {
        $modelRewards = $this->rewardsRepository->getEmptyModel();
        $modelRewards->setCustomerId((int)$customerId);
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

    /**
     * Check whether you need to update point from POS or not
     *
     * @param int $customerId
     * @return boolean
     */
    public function isNeedUpdatePointFromPos($customerId)
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->getSelect()->reset(Select::COLUMNS)->columns(EntityInterface::POINTS_SPENT);
        $orderCollection->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter(['pos_order_use_point_resend', 'pos_order_return_point_resend'],
                [['eq' => 1], ['eq' => 1]])
            ->addFieldToFilter(EntityInterface::POINTS_SPENT, ['neq' => 'NULL']);
        if ($orderCollection->count()) {
            return false;
        }
        return true;
    }
}
