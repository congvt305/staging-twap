<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 7/15/20
 * Time: 6:53 AM
 */

namespace Eguana\Magazine\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\Phrase as PhraseAlias;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Store\Ui\Component\Listing\Column\Store;

/**
 * Class for displaying Store views in Grid
 *
 * Class StoreRenderer
 *
 */
class StoreRenderer extends Store
{
    /**
     * prepare data source
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
     * Prepare store view date source
     * @param array $dataSource
     * @return PhraseAlias|string
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
