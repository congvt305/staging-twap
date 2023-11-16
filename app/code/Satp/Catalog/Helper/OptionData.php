<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Satp\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\ViewModel\Product\OptionsData;

class OptionData extends AbstractHelper
{
    /**
     * @var OptionsData
     */
    protected $optionDataViewModel;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param  OptionsData $optionDataViewModel
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        OptionsData $optionDataViewModel
    ) {
        parent::__construct($context);
        $this->optionDataViewModel = $optionDataViewModel;
    }

    /**
     * Returns options data array
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getOptionsData($product) 
    {
        return $this->optionDataViewModel->getOptionsData($product);
    }
}
