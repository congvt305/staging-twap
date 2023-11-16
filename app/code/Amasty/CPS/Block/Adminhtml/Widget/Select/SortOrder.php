<?php

namespace Amasty\CPS\Block\Adminhtml\Widget\Select;

use Amasty\CPS\Model\Product\AdminhtmlDataProvider;

class SortOrder extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Amasty\CPS\Model\Product\Sorting
     */
    private $sorting;

    /**
     * @var AdminhtmlDataProvider
     */
    private $dataProvider;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\CPS\Model\Product\Sorting $sorting,
        AdminhtmlDataProvider $dataProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sorting = $sorting;
        $this->dataProvider = $dataProvider;
    }

    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Amasty_VisualMerchUi::widget/select.phtml');
    }

    /**
     * @return array
     */
    public function getSelectOptions()
    {
        return $this->sorting->getSortingOptions();
    }

    /**
     * Get current value
     *
     * @return string
     */
    public function getSelectValue()
    {
        return $this->dataProvider->getSortOrder();
    }
}
