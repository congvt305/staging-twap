<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package HTML Sitemap for Magento 2
*/

namespace Amasty\SeoHtmlSitemap\Model\Page\Xlanding;

class PageFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create()
    {
        return $this->objectManager->create('Amasty\Xlanding\Model\Page');
    }

}