<?php

namespace Amasty\Shopby\Model\Cms;

class Page extends \Magento\Framework\Model\AbstractModel
{
    public const VAR_SETTINGS = 'amshopby_settings';

    protected function _construct()
    {
        $this->_init(\Amasty\Shopby\Model\ResourceModel\Cms\Page::class);
    }
}
