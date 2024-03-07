<?php
declare(strict_types=1);

namespace CJ\Rewards\Model\Order\Creditmemo\Action;

use Amasty\Rewards\Block\Adminhtml\Sales\Creditmemo as CreditmemoBlock;
use Amasty\Rewards\Model\Config\Source\Actions;
use Amasty\Rewards\Model\Order\Creditmemo\ActionInterface;
use Amore\PointsIntegration\Model\PointUpdate;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Sales\Model\Order\Creditmemo;

class Refund implements ActionInterface
{
    /**
     * @var PointUpdate
     */
    protected PointUpdate $pointUpdate;
    /**
     * @var MessageManager
     */
    protected MessageManager $messageManager;

    /**
     * @param PointUpdate $pointUpdate
     * @param MessageManager $messageManager
     */
    public function __construct(
        PointUpdate $pointUpdate,
        MessageManager $messageManager
    ) {
        $this->pointUpdate = $pointUpdate;
        $this->messageManager = $messageManager;
    }

    /**
     * Update point to POS and magento
     *
     * @param Creditmemo $creditmemo
     * @return void
     */
    public function execute(Creditmemo $creditmemo): void
    {
        $order = $creditmemo->getOrder();
        $refundPoint = $creditmemo->getData(CreditmemoBlock::REFUND_KEY);
        if ($refundPoint || $order->getData('am_spent_reward_points')) {
            $refundPoint = $refundPoint ?? $order->getData('am_spent_reward_points');
            $order = $creditmemo->getOrder();
            if ($order->getCustomerId()) {
                try {
                    $comment =  __('Refund #%1 for Order #%2', $creditmemo->getIncrementId(), $order->getIncrementId())->render();
                    $this->pointUpdate->pointUpdate(
                        $order,
                        $refundPoint,
                        PointUpdate::POINT_EARN,
                        PointUpdate::POINT_REASON_PURCHASE,
                        $comment,
                        Actions::REFUND_ACTION,
                        true
                    );
                } catch (\Exception $exception) {
                    $this->messageManager->addErrorMessage($exception->getMessage());
                }
            }
        }
    }
}
