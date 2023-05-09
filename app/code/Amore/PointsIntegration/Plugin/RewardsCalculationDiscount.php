<?php

namespace Amore\PointsIntegration\Plugin;

use Amore\PointsIntegration\Model\Config\Source\Actions;
use Amasty\Rewards\Model\Repository\RewardsRepository;
use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Amore\PointsIntegration\Model\PointUpdate;
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
     * @param CustomerPointsSearch $customerPointsSearch
     * @param RewardsRepository $rewardsRepository
     * @param ScopeConfigInterface $config
     * @param PointUpdate $pointUpdate
     */
    public function __construct(
        CustomerPointsSearch $customerPointsSearch,
        RewardsRepository $rewardsRepository,
        ScopeConfigInterface $config,
        PointUpdate $pointUpdate
    ) {
        $this->customerPointsSearch = $customerPointsSearch;
        $this->rewardsRepository = $rewardsRepository;
        $this->config = $config;
        $this->pointUpdate = $pointUpdate;
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
        if ($appliedPoints) {
            $appliedPoints = $this->formatPoints($appliedPoints);
            $minPointsApplied = $this->config->getValue('amrewards/points/minimum_reward');
            $spendingRate = $this->config->getValue('amrewards/points/rate');
            $multiplesPoints = $appliedPoints % $spendingRate;
            if ($appliedPoints >= $minPointsApplied &&
                $multiplesPoints == 0 && isset($quoteItems[0])
            ) {
                $firstItem = $quoteItems[0];
                $quote = $firstItem->getQuote();
                $response = [];
                if ($customerId = $quote->getCustomerId()) {
                    $websiteId = $quote->getStore()->getWebsiteId();
                    $response = $this->getCustomerPointsResults($customerId, $websiteId);
                }
//                $response['availablePoint'] = 50000;
                if ($response && $this->pointUpdate->isNeedUpdatePointFromPos($customerId)) {
                    $availablePoint = $response['availablePoint'];
                    $customerRewards = $this->rewardsRepository->getCustomerRewardBalance($customerId);
                    $pointsDiscrepancy = $availablePoint - $customerRewards;
                    if ($availablePoint < $appliedPoints) {
                        $this->pointUpdate->updatePoints($customerId, abs($pointsDiscrepancy), Actions::ACTION_DEDUCT_POINT);
                        throw new LocalizedException(__('Too much point(s) used.'));
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
                    throw new LocalizedException(__('Something went wrong while validating points.'));
                }
            } else {
                $message = __('Applied points must be greater than or equal %1 and multiples of %2',
                    $minPointsApplied, $spendingRate);
                throw new LocalizedException($message);
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
