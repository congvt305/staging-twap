<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Block\Adminhtml\Sitemap;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
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
                'label'      => __('Delete'),
                'class'      => 'delete',
                'on_click'   => 'deleteConfirm(\'' . __(
                    'Are you sure you want to delete sitemap item?'
                ) . '\', \'' . $this->getUrlBuilder()->getUrl('*/*/delete', ['sitemap_id' => $sitemapId]) . '\')',
                'sort_order' => 20,
            ];
        }

        return $data;
    }
}
