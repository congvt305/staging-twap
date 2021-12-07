<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace CJ\PointRedemption\Model\ValidationRules;

use CJ\PointRedemption\Helper\Data;
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
     * @var Data
     */
    private Data $pointRedemptionHelper;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param string $generalMessage
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        Data $pointRedemptionHelper,
        string $generalMessage = ''
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->pointRedemptionHelper = $pointRedemptionHelper;
        $this->generalMessage = $generalMessage;
    }

    /**
     * @param Quote $quote
     * @return array|ValidationResult[]
     * @throws LocalizedException
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];
        if ($this->pointRedemptionHelper->getQuoteUsedPointAmount($quote) > 0) {
            $this->pointRedemptionHelper->validatePointBalance(0, $quote);
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
