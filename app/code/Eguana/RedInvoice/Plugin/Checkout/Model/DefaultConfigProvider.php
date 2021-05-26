<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 10:10 AM
 */
declare(strict_types=1);

namespace Eguana\RedInvoice\Plugin\Checkout\Model;

use Magento\Checkout\Model\DefaultConfigProvider as DefaultConfigProviderAlias;
use Magento\Directory\Model\Country;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * This class is used to set states config data according to the website
 * Class DefaultConfigProvider
 */
class DefaultConfigProvider
{
    /**
     * Get country path
     */
    const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * @var Country
     */
    private $country;

    /**
     * DefaultConfigProvider constructor.
     * @param Country $country
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Country $country,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->country = $country;
    }

    /**
     * This function modifies default config params for checkout page
     *
     * @param DefaultConfigProviderAlias $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(DefaultConfigProviderAlias $subject, array $result): array
    {
        $countryCode = $this->getCountryByWebsite();
        $states = $this->getRegionsOfCountry($countryCode);
        $result['stateList'] = $states;
        return $result;
    }

    /**
     * Get Country code by website scope
     * @return string
     */
    public function getCountryByWebsite(): string
    {
        return $this->scopeConfig->getValue(
            self::COUNTRY_CODE_PATH,
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    /**
     * Get the list of regions present in the given Country
     * Returns empty array if no regions available for Country
     *
     * @param $countryCode
     * @return array
     */
    public function getRegionsOfCountry($countryCode)
    {
        $regionCollection = $this->country->loadByCode($countryCode)->getRegions();
        $regions = $regionCollection->loadData()->toOptionArray(false);
        return $regions;
    }
}
