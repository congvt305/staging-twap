<?php
declare(strict_types=1);

namespace CJ\Rewards\Plugin\Model\Order;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Block\Adminhtml\Sales\Creditmemo as CreditmemoBlock;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\Order\CreditmemoDocumentFactory;

class RefundPoint
{
    /**
     * Add rewards point when customer cancel order on front end
     *
     * @param CreditmemoDocumentFactory $subject
     * @param CreditmemoInterface $creditmemo
     */
    public function afterCreateFromInvoice(
        CreditmemoDocumentFactory $subject,
        CreditmemoInterface $creditmemo
    ) {
        $order = $creditmemo->getOrder();
        $refundAmount = $order->getData(EntityInterface::POINTS_SPENT);
        if ($refundAmount) {
            $creditmemo->setData(CreditmemoBlock::REFUND_KEY, $refundAmount);
            $creditmemo->setAllowZeroGrandTotal(true);
        }
        $deductAmount = $order->getData(EntityInterface::POINTS_EARN);
        if ($deductAmount) {
            $creditmemo->setData(CreditmemoBlock::EARNED_POINTS_KEY, $deductAmount);
        }
        return $creditmemo;
    }
}
