<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/18/20
 * Time: 3:14 AM
 */

namespace Eguana\Magazine\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel helper for .phtml file
 *
 * Class Magazine
 */
class Magazine implements ArgumentInterface
{
    /**
     * @var \Eguana\Magazine\Helper\Data
     */
    private $helperData;

    /**
     * Magazine constructor.
     * @param \Eguana\Magazine\Helper\Data $helperData
     */
    public function __construct(
        \Eguana\Magazine\Helper\Data $helperData
    ) {
        $this->helperData= $helperData;
    }

    /**
     * Return the top block for magazine detail page
     * @return mixed
     */
    public function getDetailTopBlockValue()
    {
        return $this->helperData->getConfig('magazine/general/detail_top_block_id');
    }

    /**
     * Return the bottom block for magazine detail page
     * @return mixed
     */
    public function getDetailBottomBlockValue()
    {
        return $this->helperData->getConfig('magazine/general/detail_bottom_block_id');
    }

    /**
     * Return the top block for magazine listin page
     * @return mixed
     */
    public function getListingTopBlockValue()
    {
        return $this->helperData->getConfig('magazine/general/listing_top_block_id');
    }

    /**
     * Return the bottom block for magazine listin page
     * @return mixed
     */
    public function getListingBottomBlockValue()
    {
        return $this->helperData->getConfig('magazine/general/listing_bottom_block_id');
    }

    /**
     * Return the sort order type from magazine configurationi
     * @return mixed
     */
    public function getSortOrderDirectionValue()
    {
        return $this->helperData->getConfig('magazine/general/sort_direction');
    }
}
