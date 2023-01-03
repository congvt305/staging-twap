<?php
declare(strict_types=1);

namespace CJ\Rewards\Plugin\Model\Quote\Collector;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Model\Calculation\Discount;
use CJ\Rewards\Model\Data;
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
        QuoteRepository $quoteRepository
    ) {
       $this->rewardsData = $rewardsData;
       $this->messageManager = $messageManager;
       $this->calculator = $calculator;
       $this->quoteRepository = $quoteRepository;
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
        if ($spentPoints && (!$this->rewardsData->canUseRewardPoint($quote) || $this->rewardsData->isExcludeDay())) {
            $quote->setData(EntityInterface::POINTS_SPENT, 0);
            $items = $shippingAssignment->getItems();
            $this->calculator->clearPointsDiscount($items);
            $this->messageManager->addErrorMessage(__('You can\'t use point right now'));
            $this->quoteRepository->save($quote);
            return $subject;
        } else {
            return $proceed($quote, $shippingAssignment, $total);
        }
    }
}
