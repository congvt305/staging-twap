<?php
namespace Sapt\Catalog\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\ActionInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;

/**
 * Check is available add to compare.
 */
class Saleableqty implements ArgumentInterface
{
    /**
     * @var GetSalableQuantityDataBySku
     */
    private $getSalableQuantityDataBySku;

    /**
     * @param UrlHelper $urlHelper
     */
    public function __construct(GetSalableQuantityDataBySku $getSalableQuantityDataBySku)
    {
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
    }


    public function getSaleableQty($sku)
    {
        $qty = $this->getSalableQuantityDataBySku->execute($sku);

        if(isset($qty[0]['qty'])){
            return $qty[0]['qty'];
        }

        return;
    }
}
