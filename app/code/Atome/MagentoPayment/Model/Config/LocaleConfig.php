<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Model\Config;

use Atome\MagentoPayment\Helper\CommonHelper;
use Atome\MagentoPayment\Model\Adapter\ConfigApi;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class LocaleConfig
{
    protected $localeDefaultInfos = [
        'sg' => [
            'currency_code'       => 'SGD',
            'minimum_spend'       => 10,
            'new_user_off'        => true,
            'new_user_off_type'   => 'AMOUNT',
            'new_user_off_amount' => 1000,
            'atome_url'           => 'https://www.atome.sg',
            'atome_logo'          => 'logo.svg',
            'checkout_logo'       => 'ic-new-user-off-sg.svg',
            'int_factor'          => 100,
            'custom_country'      => 'sg',
            'custom_lang'         => 'en',
        ],
        'hk' => [
            'currency_code'       => 'HKD',
            'minimum_spend'       => 100,
            'new_user_off'        => true,
            'new_user_off_type'   => 'AMOUNT',
            'new_user_off_amount' => 3000,
            'atome_url'           => 'https://www.atome.hk',
            'atome_logo'          => 'logo.svg',
            'checkout_logo'       => 'ic-new-user-off-hk.svg',
            'int_factor'          => 100,
            'custom_country'      => 'hk',
            'custom_lang'         => 'zh',
        ],
        'my' => [
            'currency_code'       => 'MYR',
            'minimum_spend'       => 50,
            'new_user_off'        => true,
            'new_user_off_type'   => 'AMOUNT',
            'new_user_off_amount' => 1500,
            'atome_url'           => 'https://www.atome.my',
            'atome_logo'          => 'logo.svg',
            'checkout_logo'       => 'ic-new-user-off-my.svg',
            'int_factor'          => 100,
            'custom_country'      => 'my',
            'custom_lang'         => 'en',
        ],
        'id' => [
            'currency_code'     => 'IDR',
            'minimum_spend'     => 100000,
            'atome_url'         => 'https://www.atome.id',
            'atome_logo'        => 'logo.svg',
            'checkout_logo'     => '',
            'int_factor'        => 1,
            'custom_country'    => 'id',
            'custom_lang'       => 'id',
        ],
        'th' => [
            'currency_code'     => 'THB',
            'minimum_spend'     => 100,
            'atome_url'         => 'https://www.atometh.com',
            'atome_logo'        => 'logo.svg',
            'checkout_logo'     => 'ic-new-user-off-th.svg',
            'int_factor'        => 100,
            'custom_country'    => 'th',
            'custom_lang'       => 'en',
        ],
        'vn' => [
            'currency_code'     => 'VND',
            'minimum_spend'     => 200000,
            'atome_url'         => 'https://www.atome.vn',
            'atome_logo'        => 'logo.svg',
            'checkout_logo'     => '',
            'int_factor'        => 1,
            'custom_country'    => 'vn',
            'custom_lang'       => 'vi',
        ],
        'ph' => [
            'currency_code'     => 'PHP',
            'minimum_spend'     => 80,
            'atome_url'         => 'https://www.atome.ph',
            'atome_logo'        => 'logo.svg',
            'checkout_logo'     => '',
            'int_factor'        => 100,
            'custom_country'    => 'ph',
            'custom_lang'       => 'en',
        ],
        'tw' => [
            'currency_code'     => 'TWD',
            'minimum_spend'     => 50,
            'atome_url'         => 'https://www.atome.tw',
            'atome_logo'        => 'logo.svg',
            'checkout_logo'     => '',
            'int_factor'        => 1,
            'custom_country'    => 'tw',
            'custom_lang'       => 'zh',
        ],
    ];

    protected $cacheManager;

    /**
     *  @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;
    /*
     *  @var \Atome\MagentoPayment\Helper\CommonHelper
     */
    protected $commonHelper;
    /*
     *  @var \Atome\MagentoPayment\Model\Adapter\ConfigApi
     */
    protected $configApi;

    protected $currentLocaleInfo;

    public function __construct(
        \Magento\Framework\App\Cache\Manager $cacheManager,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        CommonHelper $commonHelper,
        ConfigApi $configApi,
        PaymentGatewayConfig $paymentGatewayConfig
    ) {
        $this->cacheManager         = $cacheManager;
        $this->scopeConfig          = $scopeConfig;
        $this->configWriter         = $configWriter;
        $this->commonHelper         = $commonHelper;
        $this->configApi            = $configApi;
        $this->paymentGatewayConfig = $paymentGatewayConfig;
    }

    protected function setScopeConfigValue($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        $this->configWriter->save(
            'payment/' . \Atome\MagentoPayment\Model\PaymentGateway::METHOD_CODE . '/' . $path,
            $value,
            $scope,
            $scopeId
        );
    }

    protected function getLocaleInfo()
    {
        $localeInfoStr = $this->scopeConfig->getValue('payment/'. \Atome\MagentoPayment\Model\PaymentGateway::METHOD_CODE . '/locale_info');

        if (empty($localeInfoStr)) {
            $this->commonHelper->debug('get locale_info_str from db empty: ' . $localeInfoStr);
            return [];
        }

        $localeInfoArr = json_decode($localeInfoStr, true);

        if (empty($localeInfoArr)) {
            $this->commonHelper->debug('locale_info_str : ' . $localeInfoStr);
            $this->commonHelper->debug('locale_info_arr : ' . json_encode($localeInfoArr));
            return [];
        }

        return $localeInfoArr;
    }

    protected function saveLocaleInfo($value)
    {
        $this->setScopeConfigValue('locale_info', $value);
        $this->setScopeConfigValue('last_updated_time', time());
        $this->cacheManager->flush(['config']);
    }

    protected function getLastUpdatedTime()
    {
        return $this->scopeConfig->getValue('payment/'. \Atome\MagentoPayment\Model\PaymentGateway::METHOD_CODE . '/last_updated_time');
    }

    protected function initLocaleInfo()
    {
        $localeInfoArr   = $this->getLocaleInfo();
        $lastUpdatedTime = $this->getLastUpdatedTime();

        $country = $this->paymentGatewayConfig->getCountry();

        if (empty($lastUpdatedTime) || (time() - 3600) > $lastUpdatedTime || !isset($localeInfoArr[$country])) {
            try {
                $resp = $this->configApi->getLocaleInfo($country);
            } catch (\Exception $e) {
                $this->commonHelper->debug("get local info from atome failed: " . get_class($e) . ', message: ' . $e->getMessage());
            }

            if (isset($resp) && $resp instanceof \Atome\MagentoPayment\Model\PaymentResponse && is_array($responseData = $resp->getData())) {

                $intFactor  = $this->localeDefaultInfos[$country]['int_factor'] ?? 100;

                foreach ($responseData as $key => $value) {
                    if( 'minSpend' == $key ) {
                        $responseData['minimum_spend'] = intval($value / $intFactor);
                        unset($responseData[$key]);
                    } else {
                        $newK = preg_replace_callback('/([A-Z]{1})/', function ($matches) {
                            return '_' . strtolower($matches[0]);
                        }, $key);
                        if ($newK != $key) {
                            $responseData[$newK] = $value;
                            unset($responseData[$key]);
                        }
                    }
                }
                
                $localeInfoArr[$country] = array_merge($localeInfoArr[$country] ?? [], $responseData);
                $this->saveLocaleInfo(json_encode($localeInfoArr));
            }
        }

        $this->currentLocaleInfo = array_merge( $this->localeDefaultInfos[$country] ?? [], $localeInfoArr[$country] ?? [] );
    }

    public function __call($method, $args)
    {
        if (substr($method, 0, 3) !== 'get') {
            throw new \RuntimeException('unknown method call: ' . $method);
        }

        $k = preg_replace_callback('/([A-Z]{1})/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, lcfirst(substr($method, 3)));

        if (empty($this->currentLocaleInfo)) {
            $this->initLocaleInfo();
        }

        return $this->currentLocaleInfo[$k] ?? ($args[0] ?? '');
    }

    public function getCountryConfig()
    {
        return $this->currentLocaleInfo;
    }

    public function getSupportedCurrencyCodes()
    {
        return array_unique(array_column($this->localeDefaultInfos, 'currency_code'));
    }
}
