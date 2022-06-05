<?php

namespace Eguana\RedInvoice\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RedInvoiceOption implements OptionSourceInterface
{
    const OPTION_YES = 'Yes';
    const OPTION_NO = 'No';
    /**
     * Array
     *
     * @var array
     */
    protected $options;

    /**
     * Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $typesOfStatus = [
            0 => self::OPTION_NO,
            1 => self::OPTION_YES
        ];
        $options = [];
        foreach ($typesOfStatus as $key => $typeOfStatus) {
            $options[] = [
                'label' => $typeOfStatus,
                'value' => $key
            ];
        }
        return $options;
    }
}
