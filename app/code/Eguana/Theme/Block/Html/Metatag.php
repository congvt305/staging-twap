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
    protected $pathPrefix= '/';

    const OG_IMAGE_PATH = 'design/head/og_image';

    const DEFAULT_OG_IMAGE_PATH = 'Eguana_Theme::images/og.jpg';

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

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->_scopeConfig->getValue(self::OG_IMAGE_PATH,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $image
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImageViewUrl($image) {
        if ($image) {
            return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) .
                \Eguana\Theme\Model\Design\Backend\OGImage::UPLOAD_DIR .
                $this->pathPrefix .
                $image;
        } else {
            return $this->getViewFileUrl(self::DEFAULT_OG_IMAGE_PATH);
        }

    }
}
