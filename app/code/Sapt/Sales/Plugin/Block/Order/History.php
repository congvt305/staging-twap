<?php


namespace Sapt\Sales\Plugin\Block\Order;


use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Store\Model\StoreManagerInterface;

class History
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * History constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
    }
    public function afterGetOrders(\Magento\Sales\Block\Order\History $subject, $result)
    {
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $request = $subject->getRequest();
            $from = $request->getParam('from', false);
            $to = $request->getParam('to', false);
            /** @var Collection $result */
            if ($from && $to) {
                $result->addFieldToFilter('created_at', ['gteq' => date('Y-m-d H:i:s', strtotime($from))])
                    ->addFieldToFilter('created_at', ['lteq' => date('Y-m-d 23:59:59', strtotime($to))]);
            }
        }

        return $result;
    }
}
