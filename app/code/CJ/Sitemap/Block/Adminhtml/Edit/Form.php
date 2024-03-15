<?php

namespace CJ\Sitemap\Block\Adminhtml\Edit;

class Form extends \Magento\Sitemap\Block\Adminhtml\Edit\Form
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry             $registry,
        \Magento\Framework\Data\FormFactory     $formFactory,
        \Magento\Store\Model\System\Store       $systemStore,
        array                                   $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $systemStore, $data);
    }

    /**
     * @return Form
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('sitemap_sitemap');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('add_sitemap_form', ['legend' => __('Sitemap')]);

        if ($model->getId()) {
            $fieldset->addField('sitemap_id', 'hidden', ['name' => 'sitemap_id']);
        }

        $fieldset->addField(
            'sitemap_filename',
            'text',
            [
                'label' => __('Filename'),
                'name' => 'sitemap_filename',
                'required' => true,
                'note' => __('example: sitemap.xml'),
                'value' => $model->getSitemapFilename(),
                'class' => 'validate-length maximum-length-32'
            ]
        );

        $fieldset->addField(
            'sitemap_path',
            'text',
            [
                'label' => __('Path'),
                'name' => 'sitemap_path',
                'required' => true,
                'note' => __('example: "/media/sitemap/" for base path (path must be writeable)'),
                'value' => $model->getSitemapPath()
            ]
        );

        $fieldset->addField(
            'sitemap_exclude_urls',
            'textarea',
            [
                'label' => __('Exclude URLs'),
                'name' => 'sitemap_exclude_urls',
                'required' => false,
                'note' => __('Separate multi URLs by break line (Enter))'),
                'value' => $model->getData('sitemap_exclude_urls')
            ]
        );

        if (!$this->_storeManager->hasSingleStore()) {
            $field = $fieldset->addField(
                'store_id',
                'select',
                [
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'name' => 'store_id',
                    'required' => true,
                    'value' => $model->getStoreId(),
                    'values' => $this->_systemStore->getStoreValuesForForm()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'store_id', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }
}
