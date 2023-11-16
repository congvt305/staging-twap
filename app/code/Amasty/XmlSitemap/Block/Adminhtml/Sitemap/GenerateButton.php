<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Block\Adminhtml\Sitemap;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class GenerateButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $sitemapId = $this->getSitemapId();
        if ($sitemapId) {
            $data = [
                'label'      => __('Generate'),
                'class'      => 'generate',
                'on_click'   => 'setLocation(\'' .
                    $this->getUrlBuilder()->getUrl('*/*/generate', ['sitemap_id' => $sitemapId]) .
                    '\')',
                'sort_order' => 4,
            ];
        }

        return $data;
    }
}
