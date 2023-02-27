<?php
declare(strict_types=1);

namespace CJ\CustomTheme\Block\Html;

class Title extends \Magento\Theme\Block\Html\Title
{
    /**
     * Provide own page content heading
     *
     * @return string
     */
    public function getPageHeading()
    {
        if ($this->pageConfig->getPageLayout() == 'blog-only') {
            return null;
        }
        return parent::getPageHeading();
    }
}
