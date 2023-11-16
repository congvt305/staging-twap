<?php

declare(strict_types=1);

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\TestFramework\Helper\Bootstrap;

/** @var QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);

/** @var Quote $quoteResource */
$quoteResource = Bootstrap::getObjectManager()->get(Quote::class);

/** @var QuoteIdMaskFactory $quoteIdMaskFactory */
$quoteIdMaskFactory = Bootstrap::getObjectManager()->get(QuoteIdMaskFactory::class);

$quote = $quoteFactory->create();
$quoteResource->load($quote, 'rewards_quote', 'reserved_order_id');
$quoteResource->delete($quote);

/** @var QuoteIdMask $quoteIdMask */
$quoteIdMask = $quoteIdMaskFactory->create();
$quoteIdMask->setQuoteId($quote->getId())
    ->delete();
