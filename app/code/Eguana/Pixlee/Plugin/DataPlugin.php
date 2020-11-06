<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/5/20
 * Time: 10:42 AM
 */
declare(strict_types=1);

namespace Eguana\Pixlee\Plugin;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Pixlee\Pixlee\Helper\Data;

class DataPlugin
{
    const XML_PATH_REGION_CODE = 'pixlee_pixlee/existing_customers/account_settings/region_code';
    /**
     * @var ScopeConfigInterface
     */
    private $storeConfig;
    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct
    (
        Json $jsonSerializer,
        ScopeConfigInterface $storeConfig
    ) {
        $this->storeConfig = $storeConfig;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param \Pixlee\Pixlee\Helper\Data $subject
     * @param $result
     * @param $websiteId
     * @param $product
     */
    public function afterGetRegionalInformation(\Pixlee\Pixlee\Helper\Data $subject, $result, $websiteId, $product)
    {
        $regionCode = $this->getWebsiteRegionCodeConfig($websiteId);
        if (count($result) < 1 || !$regionCode) {
            return $result;
        }
        $newResult = [];
        foreach($result as $info) {
            $info['region_code'] = $regionCode;
            array_push($newResult, $info);
        }
        return $newResult;
    }

    /**
     * @param \Pixlee\Pixlee\Helper\Data $subject
     * @param $result
     * @param $storeId
     * @param array $extraData
     */
    public function after_preparePayload(\Pixlee\Pixlee\Helper\Data $subject, $result, $storeId, $extraData = [])
    {
        if(!$result) {
            return $result;
        }

        $regionCode = $this->getRegionCodeConfig($storeId);
        if (!$regionCode) {
            return $result;
        }

        $payload = $this->jsonSerializer->unserialize($result);
        $payload['region_code'] = $regionCode;

        return json_encode($payload);
    }

    private function getRegionCodeConfig($storeId): ?string
    {
        $regionCode = $this->storeConfig->getValue(
            self::XML_PATH_REGION_CODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $regionCode;
    }

    private function getWebsiteRegionCodeConfig($websiteId): ?string
    {
        $regionCode = $this->storeConfig->getValue(
            self::XML_PATH_REGION_CODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        return $regionCode;

    }
}