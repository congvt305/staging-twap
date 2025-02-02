<?php

namespace Amore\PointsIntegration\Plugin;

use Amasty\Rewards\Model\Config;
use CJ\Rewards\Model\Config as CJConfig;
use Amore\PointsIntegration\Model\Config\Source\Actions;
use Amasty\Rewards\Model\Repository\RewardsRepository;
use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Amore\PointsIntegration\Model\PointUpdate;
use CJ\Rewards\Model\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Plugin for Amasty\Rewards\Model\Calculation\Discount
 * @see \Amasty\Rewards\Model\Calculation\Discount
 */
class RewardsCalculationDiscount
{
    /**
     * @var CustomerPointsSearch
     */
    private $customerPointsSearch;

    /**
     * @var RewardsRepository
     */
    private $rewardsRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var PointUpdate
     */
    private $pointUpdate;

    /**
     * @var Config
     */
    private $amastyConfig;

    /**
     * @var CJConfig
     */
    private $cjCustomConfig;

    /**
     * @var Data
     */
    private $rewardsData;

    /**
     * @param CustomerPointsSearch $customerPointsSearch
     * @param RewardsRepository $rewardsRepository
     * @param ScopeConfigInterface $config
     * @param Config $amastyConfig
     * @param CJConfig $cjCustomConfig
     * @param PointUpdate $pointUpdate
     * @param Data $rewardsData
     */
    public function __construct(
        CustomerPointsSearch $customerPointsSearch,
        RewardsRepository $rewardsRepository,
        ScopeConfigInterface $config,
        Config $amastyConfig,
        CJConfig $cjCustomConfig,
        PointUpdate $pointUpdate,
        Data $rewardsData,
    ) {
        $this->customerPointsSearch = $customerPointsSearch;
        $this->rewardsRepository = $rewardsRepository;
        $this->config = $config;
        $this->amastyConfig = $amastyConfig;
        $this->cjCustomConfig = $cjCustomConfig;
        $this->pointUpdate = $pointUpdate;
        $this->rewardsData = $rewardsData;
    }

    /**
     * @param \Amasty\Rewards\Model\Calculation\Discount $subject
     * @param array $quoteItems
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param float $appliedPoints
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeCalculateDiscount(
        \Amasty\Rewards\Model\Calculation\Discount $subject,
        array $quoteItems,
        \Magento\Quote\Model\Quote\Address\Total $total,
        float $appliedPoints
    ): array
    {
        if ($appliedPoints && isset($quoteItems[0])) {
            $firstItem = $quoteItems[0];
            $quote = $firstItem->getQuote();
            if ($this->rewardsData->canUseRewardPoint($quote)) {
                $isUsePointOrMoney = $this->cjCustomConfig->isUsePointOrMoney();
                $appliedPoints = $this->formatPoints($appliedPoints);
                $minPointsApplied = $this->amastyConfig->getMinPointsRequirement(null);
                $spendingRate = $this->amastyConfig->getPointsRate();
                $multiplesPoints = $appliedPoints % $spendingRate;
                if ($appliedPoints >= $minPointsApplied && $multiplesPoints == 0) {
                    $response = [];
                    if ($customerId = $quote->getCustomerId()) {
                        if (!$this->pointUpdate->isNeedUpdatePointFromPos($customerId)) {
                            return [$quoteItems, $total, $appliedPoints];
                        }
                        $websiteId = $quote->getStore()->getWebsiteId();
                        $response = $this->getCustomerPointsResults($customerId, $websiteId);
                    }
//                $response['availablePoint'] = 50000;
                    if ($response) {
                        $availablePoint = $response['availablePoint'];
                        $customerRewards = $this->rewardsRepository->getCustomerRewardBalance($customerId);
                        $pointsDiscrepancy = $availablePoint - $customerRewards;
                        if ($availablePoint < $appliedPoints) {
                            if ($availablePoint < 0) {
                                $pointsDiscrepancy = $customerRewards;
                            }
                            $this->pointUpdate->updatePoints($customerId, abs($pointsDiscrepancy), Actions::ACTION_DEDUCT_POINT);
                            if ($isUsePointOrMoney == CJConfig::USE_MONEY_TO_GET_DISCOUNT) {
                                $errorMessage = __('Exceed limit of the deductible amount');
                            } else {
                                $errorMessage = __('Too much point(s) used.');
                            }
                            throw new LocalizedException($errorMessage);
                        } else {
                            if (abs($pointsDiscrepancy) > 0) {
                                if ($availablePoint < $customerRewards) {
                                    $this->pointUpdate->updatePoints($customerId, abs($pointsDiscrepancy), Actions::ACTION_DEDUCT_POINT);
                                }
                                if ($availablePoint > $customerRewards) {
                                    $this->pointUpdate->updatePoints($customerId, abs($pointsDiscrepancy));
                                }
                            }
                        }
                    } else {
                        throw new LocalizedException(__("Point discount function is under maintenance, you can't use this now, thanks"));
                    }
                } else {
                    $message = __('Applied points must be greater than or equal %1 and multiples of %2',
                        $minPointsApplied, $spendingRate);
                    throw new LocalizedException($message);
                }
            }
        }
        return [$quoteItems, $total, $appliedPoints];
    }

    /**
     * Get customer point result
     *
     * @param $customerId
     * @param $websiteId
     * @return array
     */
    public function getCustomerPointsResults($customerId, $websiteId): array
    {
        $customerPointsResult = $this->customerPointsSearch->getMemberSearchResult($customerId, $websiteId);

        if ($this->responseValidation($customerPointsResult)) {
            return $customerPointsResult['data'];
        } else {
            return [];
        }
    }

    /**
     * Validate response
     *
     * @param $response
     * @return bool
     */
    private function responseValidation($response): bool
    {
        $data = $response['data'] ?? null;
        if (!$data) {
            return false;
        }
        $code = $data['statusCode'] ?? null;
        if ($code && $code == '200') {
            return true;
        }
        return false;
    }

    /**
     * Format points
     *
     * @param float $appliedPoints
     * @return float
     */
    private function formatPoints(float $appliedPoints): float
    {
        return floor(number_format($appliedPoints, 0, '.', ''));
    }
}
