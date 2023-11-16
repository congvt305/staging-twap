<?php
declare(strict_types=1);

namespace Amore\StaffReferral\Model\Source;

use Amore\CustomerRegistration\Helper\Data;

class CountryCode implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array
     */
    private $options = [];

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var string[]
     */
    private $countryCodeToPhone = [
        'HK' => '852',
        'MO' => '853',
        'CN' => '86'
    ];

    /**
     * CountryCode constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            foreach ($this->helper->getCountryList() as $code => $label) {
                if (isset($this->countryCodeToPhone[$code])) {
                    $this->options[] = ['value' => $this->countryCodeToPhone[$code], 'label' => __($label)];
                }
            }
        }

        return $this->options;
    }

    /**
     * @param string $phoneCode
     * @return false|string
     */
    public function getCountryCodeFromPhone($phoneCode)
    {
        return array_search($phoneCode, $this->countryCodeToPhone);
    }
}
