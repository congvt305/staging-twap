<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 9/9/20
 * Time: 4:29 PM
 */
namespace Eguana\CustomCatalog\Plugin\Model\Quote\Item;

use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator as QuantityValidatorAlias;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Request\Http;

/**
 * This class is used for the method whcih remove the unnecessary error message
 *
 * Class QuantityValidator
 */
class QuantityValidator
{
    /**
     * @var Http
     */
    private $request;

    /**
     * QuantityValidator constructor.
     * @param Http $request
     */
    public function __construct(Http $request)
    {
        $this->request = $request;
    }

    /**
     * After plugin for validate
     * This plugin is used to remove the unnecessary error message
     * @param QuantityValidatorAlias $subject
     * @param $result
     * @param Observer $observer
     * @return mixed
     */
    public function afterValidate(QuantityValidatorAlias $subject, $result, Observer $observer)
    {
        $fullActionName = $this->request->getFullActionName();
        $quoteItem = $observer->getEvent()->getItem();
        if ($fullActionName == 'checkout_cart_index') {
            $quoteItem->removeMessageByText(__('This product is out of stock.'));
        }
        return $result;
    }
}
