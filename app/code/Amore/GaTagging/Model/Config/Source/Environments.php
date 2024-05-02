<?php
declare(strict_types=1);

namespace Amore\GaTagging\Model\Config\Source;

use Amore\GaTagging\Model\CommonVariable;

/**
 * Class Environments
 */
class Environments implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => CommonVariable::ENV_LOCAL, 'label' => __('Local')],
            ['value' => CommonVariable::ENV_DEV, 'label' => __('Development')],
            ['value' => CommonVariable::ENV_STG, 'label' => __('Staging')],
            ['value' => CommonVariable::ENV_PRD, 'label' => __('Production')]
        ];
    }
}
