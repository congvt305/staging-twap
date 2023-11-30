<?php

namespace CJ\Checkout\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class ShoppingCartAdditional implements DataPatchInterface, PatchRevertableInterface, PatchVersionInterface
{
    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @param BlockFactory $blockFactory
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        BlockFactory $blockFactory,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->blockFactory = $blockFactory;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function apply()
    {
        $swsStoreCode = 'default'; //TW Sulwhasoo store code
        $storeId = $this->storeRepository->get($swsStoreCode)->getId();
        $cmsBlockData = [
            'title' => 'Shopping Cart Additional Block',
            'identifier' => 'shopping_cart_additional',
            'content' => 'This is the content of the custom block.',
            'is_active' => 1,
            'stores' => [$storeId],
        ];

        $this->blockFactory->create()->setData($cmsBlockData)->save();
    }

    /**
     * @return void
     */
    public function revert()
    {
        $identifier = 'shopping_cart_additional';
        $block = $this->blockFactory->create()->load($identifier, 'identifier');

        if ($block->getId()) {
            $block->delete();
        }
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.0';
    }
}
