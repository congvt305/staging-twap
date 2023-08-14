<?php

namespace Amore\GaTagging\Controller\CartItem;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class GetData
 */
class GetData extends \Magento\Framework\App\Action\Action
{
    /**
     * Json Factory
     *
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var \Amore\GaTagging\Model\Ap
     */
    protected $ap;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Amore\GaTagging\Model\Ap $ap
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Amore\GaTagging\Model\Ap $ap
    ) {
        $this->resultJsonFactory = $jsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->ap = $ap;
        parent::__construct($context);
    }

    public function execute()
    {
        $cartItemId = $this->_request->getParam('itemId');
        $jsonResult = $this->resultJsonFactory->create();
        $result = [];
        $item = $this->getItemById($cartItemId);
        if ($item) {
            $currentProduct = $item->getProduct();
            $qty = $item->getQty();
            $productInfo = $this->getProductInfo($currentProduct, $qty);
            $result['productInfo'] = $productInfo;
        } else {
            $result['productInfo'] = [];
        }
        $jsonResult->setData($result);
        return $jsonResult;
    }

    /**
     * @param $product
     * @param $qty
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductInfo($product, $qty = null)
    {
        return $this->ap->getProductInfo($product, $qty);
    }

    /**
     * Retrieve item model object by item identifier
     *
     * @param   int $itemId
     * @return  \Magento\Quote\Model\Quote\Item|false
     */
    public function getItemById($itemId)
    {
        return $this->checkoutSession->getQuote()->getItemById($itemId);
    }
}
