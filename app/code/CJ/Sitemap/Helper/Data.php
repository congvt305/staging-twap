<?php

namespace CJ\Sitemap\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper{

    /**
     * @param $sitemap
     * @return array|string[]
     */
    public function getExcludeUrls($sitemap)
    {
        $excludeUrls = $sitemap->getData('sitemap_exclude_urls');
        if (!$excludeUrls) {
            return [];
        }
        return explode("\r\n", $excludeUrls);
    }
}
