<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 27/5/21
 * Time: 5:50 PM
 */
namespace Eguana\Directory\Plugin\Model;

use Magento\Directory\Model\Currency as CurrencyAlias;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http;

/**
 * This class is consists of before method which is responsible for removing decimal zero
 * for product price for vietnam site
 * Class Currency
 */
class Currency
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * @var array
     */
    protected $stores = [
        'tw_laneige',
        'vn_laneige',
        'vn_sulwhasoo'
    ];

    /**
     * Currency constructor.
     * @param StoreManagerInterface $storeManager
     * @param Http $request
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        Http $request
    ) {
        $this->storeManager = $storeManager;
        $this->localeCurrency = $localeCurrency;
        $this->localeFormat = $localeFormat;
        $this->request = $request;
    }

    /**
     * this before plugin is used to set the precision value zero
     * @param CurrencyAlias $subject
     * @param $price
     * @param $precision
     * @param array $options
     * @param bool $includeContainer
     * @param false $addBrackets
     * @return array
     */
    public function beforeFormatPrecision(
        CurrencyAlias $subject,
        $price,
        $precision,
        $options = [],
        $includeContainer = true,
        $addBrackets = false
    ) {
        $fullActionName = $this->request->getFullActionName();
        $storeCode = $this->storeManager->getStore()->getCode();
        if (in_array($storeCode, $this->stores) || $fullActionName == "mui_index_render") {
            $precision = 0;
            return [$price, $precision, $options, $includeContainer, $addBrackets];
        }
    }

    /**
     * @param CurrencyAlias $subject
     * @param callable $proceed
     * @param $price
     * @param $options
     * @return string
     * @throws \Zend_Currency_Exception
     */
    public function aroundFormatTxt(CurrencyAlias $subject, callable $proceed, $price, $options=[]) {
        if (!is_numeric($price)) {
            $price = $this->localeFormat->getNumber($price);
        }
        /**
         * Fix problem with 12 000 000, 1 200 000
         *
         * %f - the argument is treated as a float, and presented as a floating-point number (locale aware).
         * %F - the argument is treated as a float, and presented as a floating-point number (non-locale aware).
         */
        $price = sprintf("%F", $price);

        /**
         * Because numberformatter doesn't work with locale TW, so this plugin is used to remove numberformatter,
         * & to use $this->localeCurrency->getCurrency
         *
         * if ($this->canUseNumberFormatter($options)) {
         *     return $this->formatCurrency($price, $options);
         * }
         */

        return $this->localeCurrency->getCurrency($subject->getData('currency_code'))->toCurrency($price, $options);
    }
}
