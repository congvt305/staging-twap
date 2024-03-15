<?php

namespace CJ\Migrate\Model\Source;

/**
 * Class CountryId
 * @package CJ\Migrate\Model\Source
 */
class CountryId extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => 'HK', 'label' => __('Hongkong')],
                ['value' => 'MO', 'label' => __('Macao')],
                ['value' => 'CN', 'label' => __('China')],
                ['value' => 'MY', 'label' => __('Malaysia')]
            ];
        }

        return $this->_options;
    }
}
