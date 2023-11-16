<?php
namespace Amore\CustomerRegistration\Model\Source;

class CountryId extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => 'HK', 'label' => __('+852')],
                ['value' => 'MO', 'label' => __('+853')],
                ['value' => 'CN', 'label' => __('+86')],
            ];
        }

        return $this->_options;
    }
}
