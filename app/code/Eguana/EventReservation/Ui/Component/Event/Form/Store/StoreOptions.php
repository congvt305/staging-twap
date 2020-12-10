<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 24/11/20
 * Time: 11:40 AM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Ui\Component\Event\Form\Store;

use Magento\Store\Ui\Component\Listing\Column\Store\Options;

/**
 * Used to show the available stores
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
                ['value' => '', 'label' => __('Select Store')]
            ]
        );

        return $this->options;
    }
}
