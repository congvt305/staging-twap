<?php

namespace CJ\VLogicTablerate\Model\Config\Source;

class Tablerate implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \CJ\VLogicTablerate\Model\Carrier\Tablerate
     */
    protected $_carrierTablerate;

    /**
     * @param \CJ\VLogicTablerate\Model\Carrier\Tablerate $carrierTablerate
     */
    public function __construct(\CJ\VLogicTablerate\Model\Carrier\Tablerate $carrierTablerate)
    {
        $this->_carrierTablerate = $carrierTablerate;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $arr = [];
        foreach ($this->_carrierTablerate->getCode('condition_name') as $k => $v) {
            $arr[] = ['value' => $k, 'label' => $v];
        }
        return $arr;
    }
}
