<?php

declare(strict_types=1);

namespace Amasty\CPS\Observer\Admin;

use Amasty\CPS\Api\Data\BrandProductInterface;
use Amasty\CPS\Block\Adminhtml\Products;
use Amasty\CPS\Model\Product\AdminhtmlDataProvider;
use Magento\Framework\Event\ObserverInterface;

class OptionFormBuildAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var AdminhtmlDataProvider
     */
    private $dataProvider;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        \Amasty\CPS\Model\Product\AdminhtmlDataProvider $dataProvider,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->dataProvider = $dataProvider;
        $this->request = $request;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @param $result
     * @return mixed
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($observer->getData('is_slider')) {
            $this->addProductsFieldset($observer);
        }
    }

    /**
     * @param $observer
     */
    private function addProductsFieldset($observer)
    {
        $setting = $observer->getSetting();
        $form = $observer->getData('form');
        $layout = $this->layoutFactory->create();
        $storeId = (int)$this->request->getParam('store', 0);

        $this->dataProvider->clear();
        $this->dataProvider->init($setting);

        $featuredFieldset = $form->addFieldset(
            'products_fieldset',
            $this->getProductsFieldsetParams($storeId)
        );

        if ($storeId) {
            $useDefault = $featuredFieldset->addField(
                BrandProductInterface::BRAND_USE_DEFAULT_STORE_SETTING,
                'select',
                [
                    'label' => __('Use Default'),
                    'title' => __('Use Default'),
                    'note' => __('Set \'No\' to configure custom display order of products for current store view.'),
                    'name' => BrandProductInterface::BRAND_USE_DEFAULT_STORE_SETTING,
                    'required' => false,
                    'options' => [
                        '0' => __('Yes'),
                        '1' => __('No')
                    ]
                ]
            );
        }

        $block = $layout->createBlock(Products::class);
        $afterHtml = $block->toHtml();
        $elementName = 'products_fieldset-_container';
        if ($storeId) {
            $afterHtml .= $layout->createBlock(\Magento\Backend\Block\Widget\Form\Element\Dependence::class)
                ->addFieldMap($useDefault->getHtmlId(), $useDefault->getName())
                ->addFieldMap($elementName, $elementName)
                ->addFieldDependence($elementName, $useDefault->getName(), 1)->toHtml();
        }

        $featuredFieldset->addFieldset(
            $elementName,
            ['class'=>'form-inline']
        )->addField(
            'ammerch_sorting_products',
            'hidden',
            [
                'name' => 'ammerch_sorting_products',
                'after_element_html' => $afterHtml
            ]
        );
    }

    /**
     * @param int $storeId
     * @return array
     */
    private function getProductsFieldsetParams($storeId): array
    {
        $data = ['legend' => __('Products'), 'class'=>'form-inline'];
        if (!$storeId) {
            $data['comment'] = __('Please be informed that application of sorting on All Store Views level may return 
            incorrect results due to product availability status, price differences, etc. For more accurate results we 
            recommend to configure products sequence on Store View level.');
        }

        return $data;
    }
}
