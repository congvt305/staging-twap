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
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Quote\Model\Quote\Item as ItemAlias;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Status\ListFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Quote\Model\Quote\Item\OptionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Quote\Model\Quote\Item\Compare;
use Magento\Framework\Serialize\Serializer\Json;
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

    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        ListFactory $statusListFactory,
        FormatInterface $localeFormat,
        OptionFactory $itemOptionFactory,
        Compare $quoteItemCompare,
        StockRegistryInterface $stockRegistry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null,
        CartRepositoryInterface $quoteRepository,
        Http $request
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
            $serializer
        );
        $this->quoteRepository = $quoteRepository;
        $this->request = $request;
    }

    /**
     * Retrieve quote model object
     * @codeCoverageIgnore
     * @return Quote
     */
    public function getQuote()
    {
        $moduleName = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        if($this->_quote == null && $this->getQuoteId() && $controller == 'scheduled_operation') {
            $quoteId = $this->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            $this->setQuote($quote);
            $this->setQuoteId($quoteId);
        }
        return $this->_quote;
    }
}
