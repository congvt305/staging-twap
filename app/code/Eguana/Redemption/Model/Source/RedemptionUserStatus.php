<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/10/20
 * Time: 12:25 PM
 */
namespace Eguana\Redemption\Model\Source;

use Eguana\Redemption\Model\Counter;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * For values of statuses
 *
 * Class RedemptionUserStatus
 */
class RedemptionUserStatus implements OptionSourceInterface
{
    /**
     * @var Counter
     */
    private $counterModel;

    /**
     * @param Counter $counterModel
     */
    public function __construct(
        Counter $counterModel
    ) {
        $this->counterModel = $counterModel;
    }

    /**
     * Get status options
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        $availableOptions = $this->counterModel->getAvailableStatuses();
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
