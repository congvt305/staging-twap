<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Eguana\Faq\Model\Source\Category as CategoryOption;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Auth\Session;

/**
 * Class CategoryRenderer
 * This class is used to show store vise categories in admin grid
 */
class CategoryRenderer extends Column
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var CategoryOption
     */
    private $categoryOption;

    /**
     * @var Session
     */
    private $session;

    /**
     * CategoryRenderer constructor.
     * @param StoreManagerInterface $storeManager
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param CategoryOption $categoryOption
     * @param array $components
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        CategoryOption $categoryOption,
        Session $session,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
        $this->categoryOption = $categoryOption;
        $this->session = $session;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item[$name])) {
                    $item[$name] = $this->prepareItem($item);
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param array $dataSource
     * @return array|string
     */
    public function prepareItem(array $dataSource)
    {
        $userStoreId = (int)$this->session->getUser()->getRole()['gws_stores'][0];
        $categories = $this->categoryOption->toOptionArray();
        $storeId = (int)$dataSource['category'][0];
        $label = $this->storeManager->getStore($storeId)->getName();
        $content = $label . "<br/>";

        $storeCategoryList = $categories[$userStoreId];

        $categoryIndex = substr($dataSource['category'], strlen($userStoreId)) -1;

        if (array_key_exists($categoryIndex, $storeCategoryList['value'])) {
            $value = $storeCategoryList['value'][$categoryIndex]['label'];
            $content = $content . str_repeat('&nbsp;', 4) . $this->escaper->escapeHtml($value) . "<br/>";
        } else {
            $content = $this->escaper->escapeHtml(__('Wrong Category Value. Please select category again.'));
        }

        return $content;
    }
}
