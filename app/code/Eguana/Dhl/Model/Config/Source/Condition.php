<?php
declare(strict_types=1);
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 05/19/2021
 */

namespace Eguana\Dhl\Model\Config\Source;

class Condition implements \Magento\Framework\Data\OptionSourceInterface
{

    public function toOptionArray()
    {
        return [
            'package_qty' => __('# of Items vs. Destination')
        ];
    }
}
