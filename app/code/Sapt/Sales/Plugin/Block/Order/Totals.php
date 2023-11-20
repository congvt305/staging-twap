<?php


namespace Sapt\Sales\Plugin\Block\Order;


use Amasty\Rewards\Api\Data\SalesQuote\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class Totals
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Totals constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
    }
    public function afterGetTotals(\Magento\Sales\Block\Order\Totals $subject, $result)
    {
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $order = $subject->getOrder();
            if ($order) {
                $point = $this->getPointsValue($order);
                if ($point) {
                    $result[OrderInterface::POINTS_EARN] = new \Magento\Framework\DataObject(
                        [
                            'code' => OrderInterface::POINTS_EARN,
                            'value' => __('Earn %1', $this->getPointsValue($order)),
                            'label' => __('Point'),
                            'is_formated' => true,
                            'strong' => true
                        ]
                    );
                }
            }
        }

        return $result;
    }

    /**
     * @param Order $order
     * @return float|int
     */
    protected function getPointsValue(Order $order)
    {
        $amount = (float)$order->getData(OrderInterface::POINTS_EARN);
        if ($amount) {
            return round($amount, 2);
        }

        return false;
    }
}
