<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 9/17/20
 * Time: 12:07 PM
 */

namespace Amore\GaTagging\Plugin;


class Cart
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    private $quote = null;
    /**
     * @var \Amore\GaTagging\Helper\Data
     */
    private $data;

    public function __construct(
        \Amore\GaTagging\Helper\Data $data,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->data = $data;
    }

    /**
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param $result
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $storeId =
        $items =$this->getQuote()->getAllVisibleItems();

        if (is_array($result['items'])) {
            foreach ($result['items'] as $key => $itemAsArray) {
                if ($item = $this->findItemById($itemAsArray['item_id'], $items)) {
                    $result['items'][$key]['product_original_price'] = $item->getProduct()->getPrice();
                    $result['items'][$key]['product_brand'] = $this->data->getSiteName();
                }
            }
        }
        return $result;
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    private function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    private function findItemById($id, $itemsHaystack)
    {
        if (is_array($itemsHaystack)) {
            foreach ($itemsHaystack as $item) {
                /** @var $item \Magento\Quote\Model\Quote\Item */
                if ((int)$item->getItemId() == $id) {
                    return $item;
                }
            }
        }
        return false;
    }
}