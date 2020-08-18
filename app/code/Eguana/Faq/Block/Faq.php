<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Block;

use Eguana\Faq\Api\Data\FaqInterface;
use Eguana\Faq\Helper\Data;
use Eguana\Faq\Model\Faq as FaqModel;
use Eguana\Faq\Model\ResourceModel\Faq\Collection;
use Eguana\Faq\Model\ResourceModel\Faq\CollectionFactory;
use Eguana\Faq\Model\Source\Category;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;

/**
 * class Faq
 *
 * block for faq.phtml
 */
class Faq extends Template implements IdentityInterface
{

    /**
     * @var CollectionFactory
     */
    private $faqCollectionFactory;

    /**
     * @var
     */
    private $faqData;

    /**
     * @var Category
     */
    private $category;
    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Faq constructor.
     * @param Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param Category $faqCategory
     * @param UrlInterface $urlInterface
     * @param FilterProvider $filterProvider
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        Category $faqCategory,
        UrlInterface $urlInterface,
        FilterProvider $filterProvider,
        Data $helper,
        array $data = []
    ) {
        $this->faqCollectionFactory = $collectionFactory;
        $this->category = $faqCategory;
        $this->urlInterface = $urlInterface;
        $this->filterProvider = $filterProvider;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getFaqBindData()
    {
        if ($this->faqData === null) {
            $this->faqDataBind();
        }

        return $this->faqData;
    }

    /**
     * get all category options
     *
     * @return array
     */
    public function getCategoryOptionArray()
    {
        return $this->category->getOptionArray();
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function faqDataBind()
    {
        $bindData = [];

        /**
         * @var Collection $faqCollection
         * @var FaqModel $faq
         */
        $faqCollection = $this->faqCollectionFactory->create();
        $currentStoreId = $this->_storeManager->getStore()->getId();
        $faqCollection->addFieldToFilter(FaqInterface::IS_ACTIVE, ['eq' => true])
            ->addStoreFilter($currentStoreId);
        if ($this->helper->getFaqSortOrder()) {
            $faqCollection->setOrder('title', 'DESC');
        } else {
            $faqCollection->setOrder('title', 'ASC');
        }
        foreach ($faqCollection as $faq) {
            $bindData[$faq->getCategory()][] = $faq;
        }
        $this->faqData = $bindData;
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [FaqModel::CACHE_TAG];
    }

    /**
     * redirect url with search word
     *
     * @return string
     */
    public function getSearchUrl()
    {
        $url = $this->urlInterface->getBaseUrl() . 'faq/index/search';

        return $url;
    }

    /**
     * Description Filter
     * @param $description
     * @return string
     * @throws \Exception
     */
    public function descriptionFilter($description)
    {
        $filterDescription = $this->filterProvider->getPageFilter()->filter($description);
        return $filterDescription;
    }
}
