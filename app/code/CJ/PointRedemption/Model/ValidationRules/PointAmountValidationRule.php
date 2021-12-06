<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace CJ\PointRedemption\Model\ValidationRules;

use Amore\PointsIntegration\Model\CustomerPointsSearch;
use CJ\PointRedemption\Setup\Patch\Data\AddRedemptionAttributes;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use \Magento\Quote\Model\ValidationRules\QuoteValidationRuleInterface;

/**
 * @inheritdoc
 */
class PointAmountValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var string
     */
    private $generalMessage;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var CustomerPointsSearch
     */
    protected CustomerPointsSearch $customerPointsSearch;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param string $generalMessage
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        CustomerPointsSearch $customerPointsSearch,
        string $generalMessage = ''
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->generalMessage = $generalMessage;
        $this->customerPointsSearch = $customerPointsSearch;
    }

    /**
     * @param Quote $quote
     * @return array|ValidationResult[]
     * @throws LocalizedException
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];
        $validationResult = true;
        if ($this->getQuoteUsedPointAmount($quote) > 0) {
            $validationResult = $this->validatePointBalance($quote);
        }
        if (!$validationResult) {
            $validationErrors = [__($this->generalMessage)];
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    private function validatePointBalance(Quote $quote)
    {
        $customerId = $quote->getCustomerId();
        $websiteId = $quote->getStore()->getWebsiteId();
        $memberPointInfo = $this->customerPointsSearch->getMemberSearchResult($customerId, $websiteId);
        if (!isset($memberPointInfo['data']['availablePoint'])) {
            throw new LocalizedException(
                __(
                    "Point service is not available now, please try later. Sorry for the inconvenient"
                )
            );
        }
        $balanceAmount = (int)$memberPointInfo['data']['availablePoint'];
        $usedPointAmount = $this->getQuoteUsedPointAmount($quote);

        return $balanceAmount >= $usedPointAmount;
    }

    /**
     * @param $quote
     * @return float|int|mixed
     */
    private function getQuoteUsedPointAmount(Quote $quote)
    {
        $usedAmount = 0;
        foreach ($quote->getAllVisibleItems() as $item) {
            $pointAmount = $item->getData(AddRedemptionAttributes::POINT_REDEMPTION_AMOUNT_ATTRIBUTE_CODE);
            $isRedeemableProduct = $item->getData(AddRedemptionAttributes::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE);
            if ($isRedeemableProduct && $pointAmount) {
                $usedAmount = $usedAmount + ($pointAmount * $item->getQty());
            }
        }

        return $usedAmount;
    }
}
