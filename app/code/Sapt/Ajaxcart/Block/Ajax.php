<?php
namespace Sapt\Ajaxcart\Block;

class Ajax extends \Magento\Framework\View\Element\Template
{
    /**
     * Ajax cart helper.
     *
     * @var \Sapt\Ajaxcart\Helper\Data
     */
    private $ajaxHelper;

    /**
     * Catalog image helper.
     *
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * Checkout cart helper.
     *
     * @var \Magento\Checkout\Helper\Cart
     */
    private $cartHelper;

    /**
     * Pricing helper.
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;

    /**
     * ScopeConfig helper.
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $scopeConfig;

    protected $_storeManager;

    protected $localeCurrency;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Sapt\Ajaxcart\Helper\Data $ajaxHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Sapt\Ajaxcart\Helper\Data $ajaxHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency

    ) {
        $this->ajaxHelper = $ajaxHelper;
        $this->imageHelper = $imageHelper;
        $this->cartHelper = $cartHelper;
        $this->pricingHelper = $pricingHelper;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->localecurrency = $localeCurrency;


        parent::__construct($context, []);
    }

    public function getFreeShippingStatus()
    {
        return $this->scopeConfig->getValue('carriers/freeshipping/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getStoreCurrency()
    {
        $currencycode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        return $this->localecurrency->getCurrency($currencycode)->getSymbol();
    }
     /**
     * Get current store currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Get free shipping value.
     *
     * @return \Sapt\Ajaxcart\Helper\Data
     */
    public function getFreeShippingValue()
    {
        return $this->scopeConfig->getValue('carriers/freeshipping/free_shipping_subtotal', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get ajax cart helper.
     *
     * @return \Sapt\Ajaxcart\Helper\Data
     */
    public function getAjaxHelper()
    {
        return $this->ajaxHelper;
    }

    /**
     * Get catalog image helper.
     *
     * @return \Magento\Catalog\Helper\Image
     */
    public function getImageHelper()
    {
        return $this->imageHelper;
    }

    /**
     * Get checkout cart helper.
     *
     * @return \Magento\Checkout\Helper\Cart
     */
    public function getCartHelper()
    {
        return $this->cartHelper;
    }

    /**
     * Get pricing helper.
     *
     * @return \Magento\Framework\Pricing\Helper\Data
     */
    public function getPricingHelper()
    {
        return $this->pricingHelper;
    }
}
