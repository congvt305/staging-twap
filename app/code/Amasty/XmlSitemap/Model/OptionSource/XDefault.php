<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\OptionSource;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

class XDefault implements OptionSourceInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    public function toOptionArray(): array
    {
        $currentWebsite = $this->request->getParam('website');

        if ($currentWebsite) {
            $stores = $this->storeManager->getWebsite($currentWebsite)->getStores();
        } else {
            $stores = $this->storeManager->getStores();
        }

        foreach ($stores as $store) {
            $websiteId = $store->getWebsite()->getId();
            $storeId = $store->getStoreId();
            $label = $store->getName();

            if (!$currentWebsite) {
                $label = $store->getWebsite()->getName() . " — " . $label;
            }

            $options[] = [
                'label' => $label,
                'value' => $storeId,
                'website_id' => $websiteId,
            ];
        }

        usort($options, function ($a, $b) {
            $key = ($a['website_id'] === $b['website_id']) ? 'value' : 'website_id';

            return ($a[$key] < $b[$key]) ? -1 : 1;
        });
        array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);

        return $options;
    }
}
