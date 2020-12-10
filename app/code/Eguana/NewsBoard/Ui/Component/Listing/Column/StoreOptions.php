<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/11/20
 * Time: 4:50 PM
 */
namespace Eguana\NewsBoard\Ui\Component\Listing\Column;

use Magento\Store\Ui\Component\Listing\Column\Store\Options;

/**
 * Class StoreOptions
 *
 * Eguana\Faq\Ui\Component\Listing\Column
 */
class StoreOptions extends Options
{
    /**
     * toOptionArray method
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
