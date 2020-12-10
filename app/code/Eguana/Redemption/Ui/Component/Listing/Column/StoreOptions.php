<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 24/11/20
 * Time: 4:50 PM
 */
namespace Eguana\Redemption\Ui\Component\Listing\Column;

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
        array_splice(
            $this->options,
            0,
            0,
            [
                ['value' => '', 'label' => 'Select Store']
            ]
        );
        return $this->options;
    }
}
