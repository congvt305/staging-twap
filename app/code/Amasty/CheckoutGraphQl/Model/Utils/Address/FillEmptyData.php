<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Utils\Address;

use Amasty\CheckoutCore\Model\Config;

class FillEmptyData
{
    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(Config $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param array $addressInput
     * @return array
     */
    public function execute(array $addressInput): array
    {
        if (!$this->configProvider->isEnabled()) {
            return $addressInput;
        }

        $requiredFields = [
            'firstname',
            'lastname',
            'street',
            'city',
            'country_code'
        ];

        foreach ($requiredFields as $code) {
            if (empty($addressInput[$code])) {
                $defaultValue = '-';

                if ($code === 'country_code') {
                    $defaultValue = $this->configProvider->getDefaultCountryId();
                }

                $addressInput[$code] = $defaultValue;
            }
        }

        return $addressInput;
    }
}
