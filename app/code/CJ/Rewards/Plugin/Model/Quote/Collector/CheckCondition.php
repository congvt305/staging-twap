<?php
declare(strict_types=1);

namespace CJ\Rewards\Plugin\Model\Quote\Collector;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Model\Calculation\Discount;
use CJ\Rewards\Model\Data;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\QuoteRepository;

class CheckCondition
{
    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var Data
     */
    private $rewardsData;

    /**
     * @var Discount
     */
    private $calculator;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @param Data $rewardsData
     * @param MessageManagerInterface $messageManager
     * @param Discount $calculator
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        Data $rewardsData,
        MessageManagerInterface $messageManager,
        Discount $calculator,
        QuoteRepository $quoteRepository,
        RequestInterface $request
    ) {
       $this->rewardsData = $rewardsData;
       $this->messageManager = $messageManager;
       $this->calculator = $calculator;
       $this->quoteRepository = $quoteRepository;
       $this->request = $request;
    }

    /**
     * Check customer can apply rewards point or not
     *
     * @param \Amasty\Rewards\Model\Quote\Collector\Points $subject
     * @param \Closure $proceed
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return \Amasty\Rewards\Model\Quote\Collector\Points|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundCollect(
        \Amasty\Rewards\Model\Quote\Collector\Points $subject,
        \Closure $proceed,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $spentPoints = (float)$quote->getData(EntityInterface::POINTS_SPENT);
        $items = $shippingAssignment->getItems();
        if ($spentPoints && (!$this->rewardsData->canUseRewardPoint($quote) || $this->rewardsData->isExcludeDay())) {
            $quote->setData(EntityInterface::POINTS_SPENT, 0);
            $this->calculator->clearPointsDiscount($items);
            $this->messageManager->addErrorMessage(__('You can\'t use point right now'));
            $this->quoteRepository->save($quote);
            return $subject;
        } else {
            if ($this->rewardsData->isEnableShowListOptionRewardPoint()) {
                $listOptions = $this->rewardsData->getListOptionRewardPoint();
                if ($spentPoints) {
                    $amountDiscount = $listOptions[$spentPoints] ?? 0;
                    if ($quote->getGrandTotal() - $quote->getShippingAddress()->getShippingAmount() < $amountDiscount) {
                        //do not throw exception in case apply point first and then apply coupon to get discount > grand total or it will be error
                        if (preg_match('/points/', $this->request->getRequestUri())) {
                            $quote->setData(EntityInterface::POINTS_SPENT, 0);
                            $this->calculator->clearPointsDiscount($items);
                            $this->quoteRepository->save($quote);
                            throw new LocalizedException(__('Can not use rewards point because reward discount amount is  greater than grand total'));
                        }
                    }
                }
            }
            $proceed($quote, $shippingAssignment, $total);
            //some special case will make points wrong Ex: from 800 -> 799.999999998
            if ($quote->getData(EntityInterface::POINTS_SPENT) != $spentPoints) {
                $quote->setData(EntityInterface::POINTS_SPENT, $spentPoints);
            }
            return $subject;
        }
    }
}
