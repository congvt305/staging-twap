<?php
declare(strict_types=1);

namespace Amore\PointsIntegration\Model;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Model\RewardsProvider;
use Amore\PointsIntegration\Model\Connection\Request as PointRequest;
use Amore\PointsIntegration\Logger\Logger as PointsLogger;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DB\Select;
use Magento\Sales\Model\Order as MagentoOrder;
use Exception;
use Amasty\Rewards\Model\Repository\RewardsRepository;
use Amore\PointsIntegration\Model\Config\Source\Actions;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class PointUpdate
{
    const POINT_EARN = 'EARN';
    const POINT_REDEEM = 'REDEEM';

    const POINT_REASON_PURCHASE = '000010';
    const POINT_REASON_REDEMPTION = '000020';
    const POINT_REASON_EVENT = '000030';

    /**
     * @var PointsLogger
     */
    private $logger;

    /**
     * @var PointRequest
     */
    private $request;

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
     * @param RewardsRepository $rewardsRepository
     * @param PointsLogger $logger
     * @param PointRequest $request
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param OrderRepository $orderRepository
     * @param RewardsProvider $rewardsProvider
     * @param CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        RewardsRepository $rewardsRepository,
        PointsLogger $logger,
        PointRequest $request,
        CustomerRepositoryInterface $customerRepositoryInterface,
        OrderRepository $orderRepository,
        RewardsProvider $rewardsProvider,
        CollectionFactory $orderCollectionFactory
    ) {
        $this->rewardsRepository = $rewardsRepository;
        $this->logger = $logger;
        $this->request = $request;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->orderRepository = $orderRepository;
        $this->rewardsProvider = $rewardsProvider;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @param MagentoOrder $order
     * @param string $pointAmount
     * @param string $pointType
     * @param string $useReason
     * @param string $comment
     * @param string $actionType
     * @param bool $isVisibleForCustomer
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function pointUpdate(
        MagentoOrder $order,
        $pointAmount,
        string $pointType = self::POINT_EARN,
        $useReason = self::POINT_REASON_PURCHASE,
        $comment = 'Updated from POS',
        $actionType = Actions::SYSTEM_REWARDS_SYNC,
        $isVisibleForCustomer = false
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
            $dataResponse = $this->request->sendRequest(
                $dataRequest,
                $order->getStore()->getWebsiteId(),
                'pointUpdate'
            );

            $data = $this->responseValidation($dataResponse);
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
                        $this->updatePoints($order->getCustomerId(), abs($pointUpdate), $type, $comment, $actionType, $isVisibleForCustomer);
                    }
                }

                if ($pointType == self::POINT_REDEEM) {
                    $order->setData('pos_order_use_point_resend', false);
                } else {
                    $order->setData('pos_order_return_point_resend', false);
                }
                $message = null;
            }
            $this->orderRepository->save($order);
            if ($message) {
                $this->logger->error('MESSAGE: ' . $message);
                $this->logger->info($dash);
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
     * @param array $response
     * @return false|array
     */
    private function responseValidation(array $response)
    {
        $data = $response['data'] ?? null;

        if (!$data) {
            return false;
        }
        $message = $data['statusCode'] ?? null;
        if (($message == 'S') || ($message == 'E' && $data['statusMessage'] == 'The points have already been reflected.')) {
            return $data;
        }
        return false;
    }

    /**
     * Set data and update point to DB
     *
     * @param int $customerId
     * @param string|int|float $points
     * @param string $action
     * @param string $comment
     * @param string $actionType
     * @param bool $isVisibleForCustomer
     * @return void
     */
    public function updatePoints(
        $customerId,
        $points,
        $action = Actions::ACTION_ADD_POINT,
        $comment = 'Updated from POS',
        $actionType = Actions::SYSTEM_REWARDS_SYNC,
        $isVisibleForCustomer = false
    ) {
        $modelRewards = $this->rewardsRepository->getEmptyModel();
        $modelRewards->setCustomerId((int)$customerId);
        $modelRewards->setAmount((float)$points);
        $modelRewards->setComment($comment);
        $modelRewards->setAction($actionType);
        $modelRewards->setVisibleForCustomer($isVisibleForCustomer);
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
