<?php
declare(strict_types=1);

namespace CJ\SFLocker\Plugin\InventoryApi\SourceRepository;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\InventoryApi\Api\Data\SourceExtensionInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

/**
 * Set data to Source itself from its extension attributes to save these values to `inventory_source` DB table.
 */
class SaveStoreTypePlugin
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    public function __construct(
        RequestInterface $request
    )
    {
        $this->request = $request;
    }

    public function beforeSave(
        SourceRepositoryInterface $subject,
        SourceInterface           $source
    ): array
    {
        if (!$source instanceof DataObject) {
            return [$source];
        }
        $extensionAttributes = $source->getExtensionAttributes();
        if ($extensionAttributes !== null) {
            $source->setData('store_type', $extensionAttributes->getStoreType());
        }

        return [$source];
    }
}
