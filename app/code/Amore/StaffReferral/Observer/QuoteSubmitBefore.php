<?php
declare(strict_types=1);

namespace Amore\StaffReferral\Observer;

use Amore\StaffReferral\Api\Data\ReferralInformationInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class QuoteSubmitBefore implements ObserverInterface
{

    /**
     * @param Observer $observer
     * @return QuoteSubmitBefore
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getQuote();
        $order = $observer->getOrder();
        if ($baCode = $quote->getData(ReferralInformationInterface::REFERRAL_BA_CODE_KEY)) {
            $order->setData(ReferralInformationInterface::REFERRAL_BA_CODE_KEY, $baCode);
        }
        if ($ffCode = $quote->getData(ReferralInformationInterface::REFERRAL_FF_CODE_KEY)) {
            $order->setData(ReferralInformationInterface::REFERRAL_FF_CODE_KEY, $ffCode);
        }
        return $this;
    }
}
