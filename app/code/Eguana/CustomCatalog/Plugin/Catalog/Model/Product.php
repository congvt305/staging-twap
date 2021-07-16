<?php

namespace Eguana\CustomCatalog\Plugin\Catalog\Model;

use Magento\BundleStaging\Pricing\Adjustment\SelectionPriceListProvider;
use Magento\Staging\Model\VersionManager;

class Product
{
    /**
     * @var VersionManager
     */
    protected $versionManager;

    public function __construct(VersionManager $versionManager)
    {
        $this->versionManager = $versionManager;
    }

    public function afterIsSalable($subject, $response)
    {
        if ($this->versionManager->isPreviewVersion()) {
            return true;
        }

        return $response;
    }
}