<?php

namespace CJ\CatalogFaq\Block\Catalog;
use Magento\Framework\View\Element\Template;

class CatalogFaq extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }


    /**
     * get current Category
     * @return mixed|null
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * get Category Faq
     * @return mixed
     */
    public function getCategoryFaq() {
        $categoryFaq = $this->getCurrentCategory()->getData('catalog_faq');
        return !empty($categoryFaq) ? $categoryFaq : '';
    }

}
