<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 10/12/20
 * Time: 6:31 PM
 */
namespace Amore\CustomerRegistration\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject;

/**
 * Class Formfieldmap
 *
 * Form field mapping class
 */
class Formfieldmap extends AbstractFieldArray
{
    private $selectLabelRenderer;

    /**
     * @var array
     */
    protected $_columns = [];

    /**
     * @var bool
     */
    protected $_addAfter = true;

    /**
     * Get group label renderer function
     *
     * @return BlockInterface
     * @throws LocalizedException
     */
    protected function _getGroupLabelRenderer()
    {
        if (!$this->selectLabelRenderer) {
            $this->selectLabelRenderer = $this->getLayout()->createBlock(
                \Amore\CustomerRegistration\Block\Adminhtml\Form\Field\SelectGroupLabel::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->selectLabelRenderer->setClass('formfields_option_select');
        }
        return $this->selectLabelRenderer;
    }

    /**
     * Add columns to render
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn('label', ['label' => __('Customer Group Label')], ['style' => 'pointer-events: none;']);
        $this->addColumn('type', ['label' => __('Customer Group Code'), 'renderer' => $this->_getGroupLabelRenderer()]);
        $this->_addAfter = false;
    }

    /**
     * Render any cell template
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == "label") {
            $this->_columns[$columnName]['style'] = 'pointer-events: none; background: #ececec; color: #919191;';
        }
        return parent::renderCellTemplate($columnName);
    }

    /**
     * prepare row
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getGroupLabelRenderer()->calcOptionHash($row->getData('type'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
