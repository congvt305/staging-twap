<?php
namespace Sapt\Ajaxcart\Controller\Index;

use Eguana\CustomCatalog\ViewModel\GetDefaultCategory;
use Magento\Catalog\Block\Product\ProductList\Item\Container;
use Magento\Wishlist\Block\Catalog\Product\ProductList\Item\AddTo\Wishlist;
use Sapt\Ajaxcart\Filter\LocalizedToNormalized;
use Sapt\Ajaxcart\Helper\Data;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Sapt\AjaxWishlist\ViewModel\AjaxWishlistStatus;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Wishlist\Model\ItemFactory as WishlistItemFactory;
use Magento\Wishlist\Model\ResourceModel\Item as WishlistItemResource;

class Index extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * Form key validator
     *
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * Customer cart
     *
     * @var CustomerCart
     */
    protected $cart;

    /**
     * Result page factory.
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Resolver.
     *
     * @var ResolverInterface
     */
    private $resolverInterface;

    /**
     * Escaper.
     *
     * @var Escaper
     */
    private $escaper;

    /**
     * Url builder.
     *
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $loggerInterface;

    /**
     * Product repository.
     *
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Store manager.
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Ajax cart helper.
     *
     * @var Data
     */
    protected $ajaxHelper;

    /**
     * Localized to normalized.
     *
     * @var LocalizedToNormalized
     */
    private $localizedToNormalized;

    /**
     * Data object factory.
     *
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * Core registry.
     *
     * @var Registry
     */
    private $registry;

    /**
     * @var bool
     */
    private $relatedAdded = false;
    /**
     * @var GetDefaultCategory
     */
    protected $viewModel;
    /**
     * @var AjaxWishlistStatus
     */
    protected $wishlistViewModel;

    /**
     * @var WishlistHelper
     */
    protected $wishlistHelper;

    /**
     * @var WishlistItemFactory
     */
    protected $wishlistItemFactory;

    /**
     * @var WishlistItemResource
     */
    protected $wishlistItemResource;

    /**
     * @param Context $context
     * @param Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ResolverInterface $resolverInterface
     * @param Escaper $escaper
     * @param \Psr\Log\LoggerInterface $loggerInterface
     * @param PageFactory $resultPageFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param Data $ajaxHelper
     * @param LocalizedToNormalized $localizedToNormalized
     * @param DataObjectFactory $dataObjectFactory
     * @param Registry $registry
     * @param GetDefaultCategory $viewModel
     * @param AjaxWishlistStatus $wishlistViewModel
     * @param WishlistHelper $wishlistHelper
     * @param WishlistItemFactory $wishlistItemFactory
     * @param WishlistItemResource $wishlistItemResource
     */
    public function __construct(
        Context $context,
        Validator $formKeyValidator,
        CustomerCart $cart,
        ResolverInterface $resolverInterface,
        Escaper $escaper,
        \Psr\Log\LoggerInterface $loggerInterface,
        PageFactory $resultPageFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        Data $ajaxHelper,
        LocalizedToNormalized $localizedToNormalized,
        DataObjectFactory $dataObjectFactory,
        Registry $registry,
        GetDefaultCategory $viewModel,
        AjaxWishlistStatus $wishlistViewModel,
        WishlistHelper $wishlistHelper,
        WishlistItemFactory $wishlistItemFactory,
        WishlistItemResource $wishlistItemResource

    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->cart = $cart;
        $this->resolverInterface = $resolverInterface;
        $this->escaper = $escaper;
        $this->urlInterface = $context->getUrl();
        $this->loggerInterface = $loggerInterface;
        $this->resultPageFactory = $resultPageFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->ajaxHelper = $ajaxHelper;
        $this->localizedToNormalized = $localizedToNormalized;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->registry = $registry;
        $this->viewModel = $viewModel;
        $this->wishlistViewModel = $wishlistViewModel;
        $this->wishlistHelper = $wishlistHelper;
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->wishlistItemResource = $wishlistItemResource;

    }

    /**
     * Set back redirect url to response
     *
     * @param null|string $backUrl
     *
     * @return Redirect
     */
    protected function _goBack($backUrl = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($backUrl || $backUrl = $this->getBackUrl($this->_redirect->getRefererUrl())) {
            $resultRedirect->setUrl($backUrl);
        }

        return $resultRedirect;
    }

    public function getProductId()
    {
        $productId = (int)$this->getRequest()->getParam('product');

        if (!$productId) {
            $productId = (int)$this->getRequest()->getParam('id');
        }

        return $productId;
    }

    /**
     * Execute add to cart.
     *
     * @return $this|ResponseInterface|ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->ajaxHelper->isEnabled()) {
            return '';
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('/');
        }

        $params = $this->getRequest()->getParams();
        $product = $this->initProduct();
        try {
            if (array_key_exists('qty', $params)) {
                $filter = $this->localizedToNormalized;
                $params['qty'] = $filter->filter($params['qty']);
            }

            /**
             * Check product availability
             */
            if (!$product) {
                return $this->resultRedirectFactory->create()->setPath('/');
            }

            $data = [
                'status' => true,
                'added' => false,
                'messages' => []
            ];

            $result = $this->dataObjectFactory->create()->setData($data);

            $this->_eventManager->dispatch(
                'sapt_ajaxcart_add_before',
                ['product' => $product, 'request' => $this->getRequest(), 'result' => $result]
            );

            if (!$result->getData('status') && empty($messages)) {
                return $this->resultRedirectFactory->create()->setPath('/');
            }

            $this->processAddProduct($result, $product, $params);
            $this->cart->save();

            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            $resultItem = $product->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE ?
                                $this->dataObjectFactory->create()->setProduct($product)
                                : $this->registry->registry('last_added_quote_item');
            return $this->returnResult($resultItem, $this->relatedAdded);
        } catch (LocalizedException $e) {
//            $this->messageManager->addNoticeMessage(
//                $this->escaper->escapeHtml($e->getMessage())
//            );

            $result = [];
            $productId = $this->getProductId();
            $result['error'] = true;
            $result['error_info'] = $e->getMessage();
            $result['id'] = $productId;
            $result['url'] = $this->escaper->escapeUrl(
                $this->urlInterface->getUrl('ajaxcart/index/view', ['id' => $productId])
            );
            $result['view'] = true;
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($result);
            return $resultJson;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to your shopping cart right now.')
            );
            $this->loggerInterface->critical($e);

            $result = [];
            $result['error'] = true;

            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($result);
            return $resultJson;
        }
    }

    /**
     * Init requested product.
     *
     * @return bool|ProductInterface
     * @throws NoSuchEntityException
     */
    private function initProduct()
    {
        $productId = $this->getProductId();

        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Return add to cart result.
     *
     * @param \Magento\Quote\Model\Quote\Item $resultItem
     * @param boolean $relatedAdded
     * @return ResultInterface
     */
    private function returnResult($resultItem, $relatedAdded)
    {
        $result = [];
        if (!$this->cart->getQuote()->getHasError()) {
            $resultPage = $this->resultPageFactory->create();
            $popupBlock = $resultPage->getLayout()
                ->createBlock(\Sapt\Ajaxcart\Block\Ajax::class)
                ->setTemplate('Sapt_Ajaxcart::popup.phtml')
                ->setItem($resultItem);
            $relatedBlock = $resultPage->getLayout()
                ->createBlock(\Magento\Catalog\Block\Product\ProductList\Related::class)
                ->setTemplate('Magento_Catalog::product/list/items.phtml')
                ->setProduct($resultItem->getProduct())
                ->setData('view_model_get_default_name', $this->viewModel)
                ->setType('ajaxcartrelated');
            $addToBlock = $resultPage->getLayout()
                ->createBlock(Container::class);
            $wishlistBlock = $resultPage->getLayout()
                ->createBlock(Wishlist::class)
                ->setTemplate('Sapt_AjaxWishlist::catalog/product/list/addto/wishlist.phtml')
                ->setData('moduleStatusViewModel', $this->wishlistViewModel);
            $addToBlock->setChild('related.product.addto.wishlist', $wishlistBlock);
            $relatedBlock->setChild('addto', $addToBlock);
            $popupBlock->setChild('catalog.product.related', $relatedBlock);
            $html = $popupBlock->toHtml();
            $result['popup'] = $html ;
            $result['success'] = true;
            $result['id'] = $resultItem->getProduct()->getId();
        }else{
            $errors = $this->cart->getQuote()->getErrors();
            $result['popup'] = $errors[0]->getText();
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }

    /**
     * Add message from sapt_ajaxcart_add_before result
     *
     * @param array $message
     * @return void
     */
    private function addResultMessage($message)
    {
        if (isset($message['type'])) {
            switch ($message['type']) {
                case "notice":
                    $this->messageManager->addNoticeMessage(
                        $this->escaper->escapeHtml($message['message'])
                    );
                    break;
                case "error":
                    $this->messageManager->addErrorMessage(
                        $this->escaper->escapeHtml($message['message'])
                    );
                    break;
                case "success":
                    $this->messageManager->addSuccessMessage(
                        $this->escaper->escapeHtml($message['message'])
                    );
                    break;
                default:
                    $this->messageManager->addNoticeMessage(
                        $this->escaper->escapeHtml($message['message'])
                    );
            }
        }
    }

    /**
     * Process add product to cart.
     *
     * @param DataObject $result
     * @param ProductInterface $product
     * @param array $params
     * @return void
     * @throws LocalizedException
     */
    private function processAddProduct($result, $product, $params)
    {
        $messages = $result->getData('messages');
        if (!empty($messages)) {
            throw new LocalizedException(
                $messages[0]['message']
            );
        }

        if (!$result->getData('added')) {
            $this->cart->addProduct($product, $params);
            $this->removeFromWishlist($product);
        }

        $related = $this->getRequest()->getParam('related_product');
        $messages = $result->getData('messages');
        foreach ($messages as $message) {
            $this->addResultMessage($message);
        }

        if (!empty($related)) {
            $this->relatedAdded = true;
            $this->cart->addProductsByIds(explode(',', $related));
        }
    }

    /**
     * @param ProductInterface $product
     * @return void
     */
    protected function removeFromWishlist($product)
    {
        $customerWishlist = $this->wishlistHelper->getWishlist();
        if (!$customerWishlist->getId() || !$product || !$product->getId()) {
            return;
        }

        try {
            $wishlistItem = $this->wishlistItemFactory->create()->loadByProductWishlist(
                $customerWishlist->getId(),
                $product->getId(),
                $customerWishlist->getSharedStoreIds()
            );

            if (!$wishlistItem->getId()) {
                return;
            }

            $this->wishlistItemResource->delete($wishlistItem);
        } catch (\Exception $e) {
            $this->loggerInterface->error(__('Can\'t remove this product ID %1 from wishlist.', $product->getId()));
            return;
        }
    }

}
