<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 4:50 PM
 */
namespace Eguana\EventManager\Ui\Component\Listing\Column;

use Magento\Store\Ui\Component\Listing\Column\Store\Options;

/**
 * This class is used to add show the available stores
 *
 * Class StoreOptions
 */
class StoreOptions extends Options
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $this->generateCurrentOptions();
        $this->options = array_values($this->currentOptions);

        return $this->options;
    }
}
