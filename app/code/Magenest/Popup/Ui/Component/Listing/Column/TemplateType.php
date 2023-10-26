<?php
namespace Magenest\Popup\Ui\Component\Listing\Column;

use Magenest\Popup\Helper\Data;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class TemplateType extends Column
{
    /** @var Data */
    protected $_helperData;

    /**
     * TemplateType constructor.
     * @param Data $helperData
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Data $helperData,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        $components = [],
        $data = []
    ) {
        $this->_helperData = $helperData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $templateType = $this->_helperData->getTemplateType();
            foreach ($dataSource['data']['items'] as & $item) {
                $template_type = $item['template_type'];
                if ($templateType[$template_type]) {
                    $item['template_type'] = $templateType[$template_type]->getText();
                }
            }
        }
        return $dataSource;
    }
}
