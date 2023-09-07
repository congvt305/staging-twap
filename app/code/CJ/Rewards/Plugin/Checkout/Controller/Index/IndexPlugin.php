<?php
declare(strict_types=1);

namespace CJ\Rewards\Plugin\Checkout\Controller\Index;

use Amasty\Rewards\Model\Repository\RewardsRepository;
use Amasty\Rewards\Model\RewardsProvider;
use Amore\PointsIntegration\Logger\Logger;
use Amore\PointsIntegration\Model\Config\Source\Actions;
use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Amore\PointsIntegration\Model\PointUpdate;
use Amore\PointsIntegration\Model\Source\Config;
use CJ\Rewards\Model\Data;
use CJ\Rewards\Model\ReCheckAndUpdatePoint;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;

class IndexPlugin
{
    const MY_LANEIGE_STORE_CODE = 'my_laneige';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Data
     */
    private $rewardsData;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var ReCheckAndUpdatePoint
     */
    private $reCheckAndUpdatePoint;

    /**
     * @param \Magento\Customer\Model\Session $_customerSession
     * @param Data $rewardsData
     * @param Session $checkoutSession
     * @param ReCheckAndUpdatePoint $reCheckAndUpdatePoint
     */
    public function __construct(
        \Magento\Customer\Model\Session $_customerSession,
        Data $rewardsData,
        Session $checkoutSession,
        ReCheckAndUpdatePoint $reCheckAndUpdatePoint
    ) {
        $this->_customerSession = $_customerSession;
        $this->rewardsData = $rewardsData;
        $this->checkoutSession = $checkoutSession;
        $this->reCheckAndUpdatePoint = $reCheckAndUpdatePoint;
    }

    /**
     * Update point when go to checkout page
     *
     * @param \Magento\Checkout\Controller\Index\Index $subject
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeExecute(\Magento\Checkout\Controller\Index\Index $subject)
    {
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getStore()->getCode() != self::MY_LANEIGE_STORE_CODE) {
            return;
        }
        if ($this->_customerSession->isLoggedIn() && $this->rewardsData->canUseRewardPoint($quote)) {
            $customer = $this->_customerSession->getCustomer();
            $this->reCheckAndUpdatePoint->update($customer);
        }
    }

}
