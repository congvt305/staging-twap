<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 1/7/21
 * Time: 5:57 PM
 */
namespace Amore\GcrmDataExport\Override\Quote\Model\Quote;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Quote\Model\Quote\Item as ItemAlias;
use Magento\Quote\Model\Quote\Item\Option\ComparatorInterface;
use Magento\Quote\Model\Quote;

/**
 * This class overrides core module to get quotes
 * by preference
 * Class Item
 */
class Item extends ItemAlias
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Model\Status\ListFactory $statusListFactory
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param ItemAlias\OptionFactory $itemOptionFactory
     * @param ItemAlias\Compare $quoteItemCompare
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param CartRepositoryInterface $quoteRepository
     * @param Http $request
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param ComparatorInterface|null $itemOptionComparator
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Model\Status\ListFactory $statusListFactory,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory,
        \Magento\Quote\Model\Quote\Item\Compare $quoteItemCompare,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        CartRepositoryInterface $quoteRepository,
        Http $request,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        ?ComparatorInterface $itemOptionComparator = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $productRepository,
            $priceCurrency,
            $statusListFactory,
            $localeFormat,
            $itemOptionFactory,
            $quoteItemCompare,
            $stockRegistry,
            $resource,
            $resourceCollection,
            $data,
            $serializer,
            $itemOptionComparator
        );
        $this->quoteRepository = $quoteRepository;
        $this->request = $request;
    }

    /**
     * Retrieve quote model object
     *
     * @codeCoverageIgnore
     * @return Quote
     */
    public function getQuote()
    {
        $moduleName = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        if ($this->_quote == null && $this->getQuoteId() && $controller == 'scheduled_operation') {
            $quoteId = $this->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            $this->setQuote($quote);
            $this->setQuoteId($quoteId);
        }
        return $this->_quote;
    }
}
