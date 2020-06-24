<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 16/6/20
 * Time: 12:46 PM
 */

namespace Eguana\VideoBoard\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Eguana\VideoBoard\Helper\Data;

/**
 * ViewModel helper for .phtml file
 *
 * Class VideoBoard
 */
class VideoBoard implements ArgumentInterface
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * VideoBoard constructor.
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData= $helperData;
    }

    /**
     * Return the top block for banner section
     * @return string
     */
    public function getPageTopBannerValue()
    {
        return $this->helperData->getConfig(
            'videoboard/general/page_top_banner_id'
        );
    }
}
