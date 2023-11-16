<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Observer\Import\DeleteBehavior;

use Amasty\SeoToolkitLite\Model\Redirect\Product\ProcessBeforeDeletion;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class CreateRedirects implements ObserverInterface
{
    /**
     * @var ProcessBeforeDeletion
     */
    private $processBeforeDeletion;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ProcessBeforeDeletion $processBeforeDeletion,
        StoreManagerInterface $storeManager
    ) {
        $this->processBeforeDeletion = $processBeforeDeletion;
        $this->storeManager = $storeManager;
    }

    /**
     * event name: catalog_product_import_bunch_delete_commit_before
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        foreach ($observer->getEvent()->getIdsToDelete() as $id) {
            foreach ($this->storeManager->getStores(true) as $store) {
                $this->processBeforeDeletion->execute((int)$id, (int)$store->getId());
            }
        }
    }
}
