<?php

declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);

/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);

/** @var GetCategoryByName $getCategoryByName */
$getCategoryByName = $objectManager->create(GetCategoryByName::class);

/** @var  CollectionFactory $ruleCollectionFactory */
$indexBuilder = $objectManager->get(IndexBuilder::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    $product = $productRepository->get('rew333SimPro', false, null, true);
    $productRepository->delete($product);
} catch (NoSuchEntityException $e) {
    //product already deleted.
}

$category = $getCategoryByName->execute('Cat rew sim prod');

try {
    $categoryRepository->delete($category);
} catch (NoSuchEntityException $e) {
    //category already deleted.
}

$indexBuilder->reindexFull();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
