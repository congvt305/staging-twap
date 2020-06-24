<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Request\DataPersistor;
use Eguana\Faq\Api\FaqRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Eguana\Faq\Model\Source\Category;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Search
 *
 * Eguana\Faq\ViewModel
 */
class Search implements ArgumentInterface
{
    /**
     * @var DataPersistor
     */

    private $dataPersistor;
    /**
     * @var FaqRepositoryInterface
     */

    private $faqRepository;
    /**
     * @var SearchCriteriaBuilder
     */

    private $searchCriteriaBuilder;
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Search constructor.
     * @param Template\Context $context
     * @param DataPersistor $dataPersistor
     * @param FaqRepositoryInterface $faqRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Category $category
     * @param UrlInterface $urlInterface
     * @param FilterProvider $filterProvider
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        DataPersistor $dataPersistor,
        FaqRepositoryInterface $faqRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Category $category,
        UrlInterface $urlInterface,
        FilterProvider $filterProvider,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->faqRepository = $faqRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->category = $category;
        $this->urlInterface = $urlInterface;
        $this->filterProvider = $filterProvider;
        $this->storeManager = $storeManager;
    }

    /**
     * string of search word
     *
     * get search word using dataPersistor class
     *
     * @return string
     */
    public function getSearchValue()
    {
        $searchValue = $this->dataPersistor->get('searchValue');

        return $searchValue;
    }

    /**
     * get data from faq repository
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    public function getSearchData()
    {
        $currentStoreId = $this->storeManager->getStore()->getId();

        $search = $this->searchCriteriaBuilder->addFilter('is_active', 1)
            ->addFilter('store_id', $currentStoreId)->create();

        $faqData = $this->faqRepository->getList($search)->getItems();

        $searchValue = $this->getSearchValue();

        $buildData = $this->dataBuild($faqData, $searchValue);

        return $buildData;
    }

    /**
     * provide proper search matching data
     *
     * @param $faqData
     * @param $searchValue
     * @return array
     */
    private function dataBuild($faqData, $searchValue)
    {
        $data = [];

        foreach ($faqData as $key => $value) {
            $description = $this->getDescriptionInsideTag($value->getDescription());

            $categoryName = $this->getFaqCategoryName($value->getCategory());

            if (stripos($value->getTitle(), $searchValue)!==false) {
                $search = $value->getData();
                $data[$categoryName][]= $search;
                continue;
            }

            if (stripos($description, $searchValue)!==false) {
                $search = $value->getData();
                $data[$categoryName][]= $search;
                continue;
            }

            if (stripos($categoryName, $searchValue)!==false) {
                $search = $value->getData();
                $data[$categoryName][]= $search;
                continue;
            }
        }

        return $data;
    }

    /**
     * get category name using category ids
     *
     * @param $categoryId
     * @return string
     */
    private function getFaqCategoryName($categoryId)
    {
        $categoryName = $this->category->categoryNameToSearch($categoryId);

        return $categoryName;
    }

    /**
     * redirect url with search word
     *
     * @param $path
     * @return string
     */
    public function getSearchUrl()
    {
        $url = $this->urlInterface->getBaseUrl().'faq/index/search';

        return $url;
    }

    /**
     * remove html tag in description(magento page builder create html tag in description)
     *
     * @param $rowDescription
     * @return string
     */
    private function getDescriptionInsideTag($rowDescription)
    {
        $strip_tag =  strip_tags($rowDescription);

        return $strip_tag;
    }

    /**
     * highlight search value
     *
     * @param $str
     * @param $searchValue
     * @return mixed
     */
    public function highlightSearchValue($str, $searchValue)
    {
        $occurrences = substr_count(strtolower($str), strtolower($searchValue));
        $newString = $str;
        $match = [];

        for ($i=0; $i < $occurrences; $i++) {
            $match[$i] = stripos($str, $searchValue, $i);
            $match[$i] = substr($str, $match[$i], strlen($searchValue));
            $newString = str_replace($match[$i], '[#]' . $match[$i] . '[@]', $newString);
        }
        $newString = strip_tags($newString);
        $newString = str_replace('[#]', '<b class="search-word">', $newString);
        $newString = str_replace('[@]', '</b>', $newString);
        return $newString;
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

    /**
     * Get Store Id
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
