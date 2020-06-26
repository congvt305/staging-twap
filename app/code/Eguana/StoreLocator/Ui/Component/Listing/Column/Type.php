<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 24/6/20
 * Time: 5:10 PM
 */
namespace Eguana\StoreLocator\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;

class Type implements OptionSourceInterface
{
    protected $_options;

    public function toOptionArray()
    {
        if ($this->_options == null) {
            $this->_options[] = [
                'value' => 'FS',
                'label' => "Flagship Store"
            ];
            $this->_options[] = [
                'value' => 'RS',
                'label' => "Road Shop Store"
            ];
        }
        return $this->_options;
    }
}
