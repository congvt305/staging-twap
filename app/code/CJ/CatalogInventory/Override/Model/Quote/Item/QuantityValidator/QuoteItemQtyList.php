<?php

namespace CJ\CatalogInventory\Override\Model\Quote\Item\QuantityValidator;

class QuoteItemQtyList extends \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList
{
    /**
     * Reset singleton data
     *
     * @return void
     */
    public function resetCheckQuoteItems() {
        $this->_checkedQuoteItems = [];
    }
}
