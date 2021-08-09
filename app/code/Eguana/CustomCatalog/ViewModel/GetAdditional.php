<?php
/**
 * Created by PhpStorm
 * User: Phat Pham
 * Date: 9 Aug 2021
 * Time: 2:25 PM
 */
namespace Eguana\CustomCatalog\ViewModel;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class GetAdditional
 *
 */
class GetAdditional implements ArgumentInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    private $_category = null;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository
    )
    {
        $this->_storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
    }


    public function getCurrencyCode() {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * Retrieve product category
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory($id)
    {
        if ($this->_category === null && $id) {
            $category = $this->categoryRepository->get($id);
            $this->_category = $category;
        }
        return $this->_category;
    }

}
