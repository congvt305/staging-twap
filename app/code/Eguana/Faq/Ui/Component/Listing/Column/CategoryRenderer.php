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

/**
 * Class CategoryRenderer
 *
 * Eguana\Faq\Ui\Component\Listing\Column
 */
class CategoryRenderer extends Column
{
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var CategoryOption
     */
    private $categoryOption;

    /**
     * CategoryRenderer constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param CategoryOption $categoryOption
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        CategoryOption $categoryOption,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->escaper = $escaper;
        $this->categoryOption = $categoryOption;
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
        $categories = $this->categoryOption->toOptionArray();

        $label = $categories[$dataSource['store_id'][0]]['label'];
        $content = $label . "<br/>";

        $storeCategoryList = $categories[$dataSource['store_id'][0]];

        $categoryIndex = substr($dataSource['category'], strlen($dataSource['store_id'][0])) -1;

        if (array_key_exists($categoryIndex, $storeCategoryList['value'])) {
            $value = $storeCategoryList['value'][$categoryIndex]['label'];
            $content = $content . str_repeat('&nbsp;', 4) . $this->escaper->escapeHtml($value) . "<br/>";
        } else {
            $content = $this->escaper->escapeHtml(__('Wrong Category Value. Please select category again.'));
        }

        return $content;
    }
}
