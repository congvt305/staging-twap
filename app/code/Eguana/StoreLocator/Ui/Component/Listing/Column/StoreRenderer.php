<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 12/06/19
 * Time: 12:54 PM
 */
namespace Eguana\StoreLocator\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Store\Ui\Component\Listing\Column\Store;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 * Class StoreRenderer
 *  Eguana\StoreLocator\Ui\Component\Listing\Column
 */
class StoreRenderer extends Store
{
    /**
     * StoreRenderer constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SystemStore $systemStore,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $systemStore, $escaper, $components, $data, 'store_id');
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item[$name])) {
                    $item[$name] = $this->prepareItem($item);
                }
            }
        }
        return $dataSource;
    }

    /**
     * Prepare Store views data source
     *
     * @param array $dataSource
     * @return string
     */
    protected function prepareItem(array $dataSource)
    {
        $origStores = null;
        if (isset($dataSource[$this->storeKey])) {
            $origStores = $dataSource[$this->storeKey];
        }
        if (!is_array($origStores)) {
            $origStores = explode(',', $origStores);
        }
        if (in_array(0, $origStores) && count($origStores) == 1) {
            return __('All Store Views');
        }
        $content = '';
        $data = $this->systemStore->getStoresStructure(false, $origStores);
        foreach ($data as $website) {
            $content .= $website['label'] . "<br/>";
            foreach ($website['children'] as $group) {
                $content .= str_repeat('&nbsp;', 4) . $this->escaper->escapeHtml($group['label']) . "<br/>";
                foreach ($group['children'] as $store) {
                    $content .= str_repeat('&nbsp;', 8) . $this->escaper->escapeHtml($store['label']) . "<br/>";
                }
            }
        }
        return $content;
    }
}
