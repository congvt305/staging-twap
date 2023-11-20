<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty Advanced Reviews PageBuilder for Magento 2 (System)
*/

namespace Amasty\ReviewPageBuilder\Model\DataProvider;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * Return an empty array as data as we populate through the browser
     *
     * @return array
     */
    public function getData()
    {
        return [];
    }
}
