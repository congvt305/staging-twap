<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 2:00 PM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Model\Source;

use Eguana\Redemption\Model\Redemption;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class to convert labels on admin panel
 *
 * Class Status
 */
class IsActive implements OptionSourceInterface
{
    /**
     * @var Redemption
     */
    private $redemption;

    /**
     * IsActive constructor.
     *
     * @param Redemption $redemption
     */
    public function __construct(Redemption $redemption)
    {
        $this->redemption = $redemption;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        $availableOptions = $this->redemption->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
