<?php

namespace Eguana\Redemption\Ui\Component\Form\Block;

class EmailTemplates extends \Magento\Config\Model\Config\Source\Email\Template
{
    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry                                   $coreRegistry,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Email\Model\Template\Config                          $emailConfig,
        array                                                         $data = []
    )
    {
        parent::__construct($coreRegistry, $templatesFactory, $emailConfig, $data);
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = parent::toOptionArray();
        array_unshift($options, ['value' => '', 'label' => __('Select Email Template')]);
        return $options;
    }
}
