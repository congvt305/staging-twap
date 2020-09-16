<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Eguana\Theme\Block\Html;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * OpenGraph & Twitter Meta tag
 */
class Metatag extends Template
{
    public function getKeywords()
    {
        return $this->pageConfig->getKeywords();
    }

    public function getMetaTitle()
    {
        return $this->pageConfig->getTitle()->get();
    }

    public function getDescription()
    {
        return $this->pageConfig->getDescription();
    }

    public function getCurrentUrl()
    {
        return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
    }
}
