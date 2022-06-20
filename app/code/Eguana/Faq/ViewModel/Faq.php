<?php
declare(strict_types=1);

namespace Eguana\Faq\ViewModel;

use Eguana\Faq\Helper\Data;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Faq implements ArgumentInterface
{
    /**
     * @var Data
     */
    private Data $faqHelper;

    /**
     * @param Data $faqHelper
     */
    public function __construct(
        Data $faqHelper
    ) {
        $this->faqHelper = $faqHelper;
    }

    /**
     * Get product
     *
     * @return Product|mixed|null
     */
    public function getProduct()
    {
        return $this->faqHelper->getProduct();
    }

    /**
     * Get category
     *
     * @return Category|mixed|null
     */
    public function getCategory()
    {
        return $this->faqHelper->getCategory();
    }

    /**
     * Get data for specific faq
     *
     * @param int $pageSize
     * @param int $curPage
     * @return false|\Magento\Framework\DataObject[]|string[]|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFaqData($pageSize = 0, $curPage = 0)
    {
        return $this->faqHelper->getFaqData($pageSize, $curPage);
    }

    /**
     * Get header for faq
     *
     * @return null
     */
    public function getHeaderFaq()
    {
        return $this->faqHelper->getHeaderFaq();
    }

    /**
     * IsPdpPage
     *
     * @return bool
     */
    public function isPdpPage()
    {
        return $this->faqHelper->isPdpPage();
    }
}
