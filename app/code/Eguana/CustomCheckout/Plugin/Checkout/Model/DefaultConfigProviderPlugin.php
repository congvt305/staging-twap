<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2020-06-02
 * Time: 오후 7:19
 */

namespace Eguana\CustomCheckout\Plugin\Checkout\Model;

use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\ProductRepository as ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;

class DefaultConfigProviderPlugin extends AbstractModel
{
    protected $checkoutSession;

    protected $_productRepository;

    /**
     * DefaultConfigProviderPlugin constructor.
     * @param CheckoutSession $checkoutSession
     * @param ProductRepository $productRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ProductRepository $productRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->_productRepository = $productRepository;
    }

    /**
     * @param DefaultConfigProvider $subject
     * @param array $result
     * @return array
     * @throws NoSuchEntityException
     */
    public function afterGetConfig(
        DefaultConfigProvider $subject,
        array $result
    ) {
        $items = $result['totalsData']['items'];
        foreach ($items as $index => $item) {
            if (!isset($item['item_id'])) {
                continue;
            }
            $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);
            $product = $this->_productRepository->getById($quoteItem->getProduct()->getId());

            if ($product->getCustomAttribute('prdvl')) {
                $result['quoteItemData'][$index]['ap_size'] =
                    $product->getPrdvl().$product->getAttributeText('vlunt');
                if ($product->getCustomAttribute('product_count')) {
                    $size = $result['quoteItemData'][$index]['ap_size'];
                    $result['quoteItemData'][$index]['ap_size'] =
                        $size.'*'.$product->getAttributeText('product_count');
                }
            } else {
                $result['quoteItemData'][$index]['ap_size'] = '';
            }
        }
        return $result;
    }
}
