<?php
namespace Magenest\Popup\Model\Source\Popup;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class PopupTemplate extends AbstractSource implements SourceInterface, OptionSourceInterface
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    /**
     * @var \Magenest\Popup\Model\TemplateFactory
     */
    protected $_templateModel;

    /**
     * PopupTemplate constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magenest\Popup\Model\TemplateFactory $templateModel
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magenest\Popup\Model\TemplateFactory $templateModel
    ) {
        $this->_registry = $registry;
        $this->_templateModel = $templateModel;
    }

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        $templateFactory = $this->_templateModel->create();
        $collections = $templateFactory->getCollection();
        $arr = [];
        foreach ($collections as $collection) {
            $arr[$collection->getTemplateId()] =$collection->getTemplateName();
        }
        return $arr;
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
