<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/6/20
 * Time: 2:49 PM
 */
namespace Eguana\VideoBoard\Ui\Component\Listing\Column;

use Magento\Store\Ui\Component\Listing\Column\Store\Options;

/**
 * This class is used to add show the available stores
 *
 * Class StoreOptions
 * Eguana\VideoBoard\Ui\Component\Listing\Column
 */
class StoreOptions extends Options
{
    /**
     * All store views value is 0
     */
    const ALL_STORE_VIEWS = '0';

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
