<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 6/8/20
 * Time: 3:57 PM
 */
namespace Eguana\CustomCheckout\Plugin\Model;

use Amasty\Promo\Helper\Item as ItemAlias;
use Magento\Checkout\Model\DefaultConfigProvider as DefaultConfigProviderAlias;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * DefaultConfigProvider Model This class is used for config data in cart
 *
 * Class DefaultConfigProvider
 */
class DefaultConfigProvider
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ItemAlias
     */
    protected $promoItemHelper;

    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * DefaultConfigProvider constructor.
     * @param LoggerInterface $logger
     * @param ItemAlias $promoItemHelper
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        ItemAlias $promoItemHelper,
        LoggerInterface $logger,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->logger = $logger;
        $this->promoItemHelper = $promoItemHelper;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * This after plugin is checking is cart item gift product or not
     * @param DefaultConfigProviderAlias $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(DefaultConfigProviderAlias $subject, array $result)
    {
        try {
            if (isset($result['quoteItemData'])) {
                $items = $result['quoteItemData'];
                foreach ($items as $index => $item) {
                    $quoteId = $result['quoteItemData'][$index]['quote_id'];
                    $itemId = $result['quoteItemData'][$index]['item_id'];
                    $quote = $this->quoteRepository->getActive($quoteId);
                    $quoteItem = $quote->getItemById($itemId);
                    if ($this->promoItemHelper->isPromoItem($quoteItem)) {
                        $result['quoteGifts'][$itemId] = true;
                    } else {
                        $result['quoteGifts'][$itemId] = false;
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->info('CustomCheckout | afterGetConfig exception message', [$exception->getMessage()]);
            return $result;
        }
        return $result;
    }
}
