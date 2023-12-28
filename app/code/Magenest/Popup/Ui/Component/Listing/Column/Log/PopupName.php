<?php
namespace Magenest\Popup\Ui\Component\Listing\Column\Log;

use Magenest\Popup\Model\PopupFactory;
use Magenest\Popup\Model\ResourceModel\Popup;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class PopupName extends \Magento\Ui\Component\Listing\Columns\Column
{
    /** @var PopupFactory */
    protected $_popupFactory;

    /** @var Popup */
    private $popupResources;

    /**
     * PopupName constructor.
     * @param PopupFactory $popupFactory
     * @param Popup $popupResources
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        PopupFactory $popupFactory,
        Popup $popupResources,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        $components = [],
        $data = []
    ) {
        $this->_popupFactory = $popupFactory;
        $this->popupResources = $popupResources;
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
            $popupModel = $this->_popupFactory->create();
            foreach ($dataSource['data']['items'] as & $item) {
                $this->popupResources->load($popupModel, $item['popup_id']);
                if ($popupModel->getPopupId()) {
                    $item['popup_id'] = $popupModel->getPopupName();
                }
                $popupModel->unsetData();
            }
        }
        return $dataSource;
    }
}
