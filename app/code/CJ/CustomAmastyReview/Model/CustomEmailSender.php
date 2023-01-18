<?php

namespace CJ\CustomAmastyReview\Model;

use Amasty\AdvancedReview\Model\Email\CouponDataProvider;

/**
 * Class CustomEmailSender
 */
class CustomEmailSender
{
    /**
     * @var \CJ\CustomAmastyReview\Helper\Config
     */
    protected $dataHelper;

    /**
     * @var \Amasty\AdvancedReview\Model\Repository\ReminderRepository
     */
    protected $reminderRepository;

    /**
     * @param \CJ\CustomAmastyReview\Helper\Config $dataHelper
     */
    public function __construct(
        \CJ\CustomAmastyReview\Helper\Config $dataHelper,
        \Amasty\AdvancedReview\Model\Repository\ReminderRepository $reminderRepository
    ) {
        $this->dataHelper = $dataHelper;
        $this->reminderRepository = $reminderRepository;
    }

    /**
     * @param $id
     * @param $websiteId
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateCouponStatus($id, $websiteId)
    {
        if ($this->dataHelper->isAllowCoupons($websiteId)) {
            $reminder = $this->reminderRepository->getById($id);
            $reminder->setCoupon(CouponDataProvider::STATUS_ACTIVE);
            $reminder->save();
        }
    }
}
