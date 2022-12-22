<?php
namespace Atome\MagentoPayment\Services\View\Source;

use Magento\Framework\Option\ArrayInterface;

class CategoryList implements ArrayInterface
{
    protected $categoryHelper;
    protected $categoryRepository;
    protected $categoryList;

    public function __construct(
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository
    ) {
        $this->categoryHelper = $catalogCategory;
        $this->categoryRepository = $categoryRepository;
    }

    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->categoryHelper->getStoreCategories($sorted, $asCollection, $toLoad);
    }

    public function toOptionArray()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/log-error-atome-payment.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('start render exclude category');
        try {
            $arr = $this->toArray();
            $ret = [];
            foreach ($arr as $key => $value) {
                $ret[] = ['value' => $key, 'label' => $value];
            }

            $logger->info('array category : ' . json_encode($arr));
        } catch (\Exception $e) {
            $logger->info($e->getMessage());
        }

        return $ret;
    }

    public function toArray()
    {
        $categories = $this->getStoreCategories(true, false, true);
        return $this->renderCategories($categories);
    }

    public function renderCategories($_categories)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/log-error-atome-payment.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $count = 0;
        $this->categoryList = [];
        foreach ($_categories as $category) {
            $i = 0;
            $this->categoryList[$category->getEntityId()] = __($category->getName());   // Main categories
            $this->renderSubCat($category, $i);
            $count++;
        }
        $logger->info('total count : ' . $count);
        return $this->categoryList;
    }

    public function renderSubCat($cat, $j)
    {
        $categoryObj = $this->categoryRepository->get($cat->getId());

        $level = $categoryObj->getLevel();
        $arrow = str_repeat(". . . ", $level - 1);
        $subcategories = $categoryObj->getChildrenCategories();

        foreach ($subcategories as $subcategory) {
            $this->categoryList[$subcategory->getEntityId()] = __($arrow . $subcategory->getName());
            if ($subcategory->hasChildren()) {
                $this->renderSubCat($subcategory, $j);
            }
        }
        return $this->categoryList;
    }
}
