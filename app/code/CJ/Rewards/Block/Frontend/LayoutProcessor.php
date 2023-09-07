<?php

namespace CJ\Rewards\Block\Frontend;

use Amasty\Rewards\Api\CheckoutHighlightManagementInterface;
use Amasty\Rewards\Api\GuestHighlightManagementInterface;
use Amasty\Rewards\Model\Config as ConfigProvider;
use Amasty\Rewards\Model\RewardsPropertyProvider;
use CJ\Rewards\Model\Config;
use CJ\Rewards\Model\Data;
use Magento\Checkout\Model\Session;

class LayoutProcessor extends \Amasty\Rewards\Block\Frontend\LayoutProcessor
{
    /**
     * @var RewardsPropertyProvider
     */
    private $rewardsPropertyProvider;

    /**
     * @var CheckoutHighlightManagementInterface
     */
    private $highlightManagement;

    /**
     * @var GuestHighlightManagementInterface
     */
    private $guestHighlightManagement;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Data
     */
    private $rewardsData;

    /**
     * @var Session
     */
    private $checkouSession;

    /**
     * @param RewardsPropertyProvider $rewardsPropertyProvider
     * @param CheckoutHighlightManagementInterface $highlightManagement
     * @param GuestHighlightManagementInterface $guestHighlightManagement
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        RewardsPropertyProvider $rewardsPropertyProvider,
        CheckoutHighlightManagementInterface $highlightManagement,
        GuestHighlightManagementInterface $guestHighlightManagement,
        ConfigProvider $configProvider,
        Config $config,
        Data $rewardsData,
        Session $checkouSession
    ) {
        parent::__construct(
            $rewardsPropertyProvider,
            $highlightManagement,
            $guestHighlightManagement,
            $configProvider
        );
        $this->rewardsPropertyProvider = $rewardsPropertyProvider;
        $this->highlightManagement = $highlightManagement;
        $this->guestHighlightManagement = $guestHighlightManagement;
        $this->configProvider = $configProvider;
        $this->config = $config;
        $this->rewardsData = $rewardsData;
        $this->checkouSession = $checkouSession;
    }

    /**
     * Add data for rewards
     *
     * @param array $jsLayout
     *
     * @return array
     */
    public function process($jsLayout)
    {
        $highlightPath = 'components/checkout/./sidebar/./summary/./amasty-rewards-highlight';

        if (!$this->configProvider->isEnabled()) {
            $this->unsetArrayValueByPath(
                $jsLayout,
                'components/checkout/./steps/./billing-step/./payment/./beforeMethods/./rewards'
            );
            $this->unsetArrayValueByPath(
                $jsLayout,
                $highlightPath
            );

            return $jsLayout;
        }

        $this->setToArrayByPath(
            $jsLayout,
            'components/checkout/./steps/./billing-step/./payment/./beforeMethods/./rewards',
            $this->getRewardData()
        );

        if ($this->highlightManagement->isVisible(CheckoutHighlightManagementInterface::CHECKOUT)) {
            $this->setToArrayByPath(
                $jsLayout,
                $highlightPath,
                $this->highlightManagement->getHighlightData()
            );
        } elseif ($this->guestHighlightManagement->isVisible(GuestHighlightManagementInterface::PAGE_CHECKOUT)) {
            $this->setToArrayByPath(
                $jsLayout,
                $highlightPath . '/component',
                'Amasty_Rewards/js/guest-highlight',
                false
            );
            $this->setToArrayByPath(
                $jsLayout,
                $highlightPath . '/highlight',
                $this->guestHighlightManagement
                    ->getHighlight(GuestHighlightManagementInterface::PAGE_CHECKOUT)
                    ->getData()
            );
        } else {
            $this->unsetArrayValueByPath($jsLayout, $highlightPath);
        }

        return $jsLayout;
    }

    /**
     * Get list of reward data
     *
     * @return array
     */
    private function getRewardData()
    {
        $rewardsData = $this->rewardsPropertyProvider->getRewardsData();
        $quote = $this->checkouSession->getQuote();
        if ($canUseRewardPoint = $this->rewardsData->canUseRewardPoint($quote)) {
            $rewardsData['canUseRewardPoint'] = $canUseRewardPoint;
            if ($this->rewardsData->isEnableShowListOptionRewardPoint()) {
                $arrData = [];
                foreach ($this->rewardsData->getListOptionRewardPoint() as $point => $amount) {
                    $arrData[] = [
                        'point' => $point,
                        'amount' => $amount
                    ];
                }
                $rewardsData['listOption'] = $arrData;
            }
        }

        return $rewardsData;
    }
}
