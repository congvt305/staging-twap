<?php
declare(strict_types=1);

namespace CJ\Rewards\Observer;

use Amasty\Rewards\Block\Adminhtml\Sales\Creditmemo as CreditmemoBlock;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Sales\Model\Order\Creditmemo;
use Amore\PointsIntegration\Model\PointUpdate;
class UpdatePointToPos implements ObserverInterface
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
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {

        /** @var Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();
        $refundPoint = $creditmemo->getData(CreditmemoBlock::REFUND_KEY);
        if ($refundPoint || $order->getData('am_spent_reward_points')) {
            $refundPoint = $refundPoint ?? $order->getData('am_spent_reward_points');
            $order = $creditmemo->getOrder();
            if ($order->getCustomerId()) {
                try {
                    $this->pointUpdate->pointUpdate($order, $refundPoint);
                } catch (\Exception $exception) {
                    $this->messageManager->addErrorMessage($exception->getMessage());
                }
            }
        }
    }
}
