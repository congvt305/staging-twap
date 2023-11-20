<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sapt\AjaxWishlist\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Product\AttributeValueProvider;

/**
 * Wishlist Remove Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RemoveAll extends \Magento\Wishlist\Controller\AbstractIndex implements Action\HttpPostActionInterface
{
    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var AttributeValueProvider
     */
    private $attributeValueProvider;

    /**
     * @param Action\Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param Validator $formKeyValidator
     * @param AttributeValueProvider|null $attributeValueProvider
     */
    public function __construct(
        Action\Context $context,
        WishlistProviderInterface $wishlistProvider,
        Validator $formKeyValidator
    ) {
        $this->wishlistProvider = $wishlistProvider;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    /**
     * Remove item
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws NotFoundException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }
        $wishlistId = $this->getRequest()->getParam('wishlist_id', 0);
        $wishlist = $this->wishlistProvider->getWishlist($wishlistId);
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found.'));
        }
        try {
            /** @var Item $item */
            foreach($wishlist->getItemCollection() as $item) {
                $item->delete();
            }
            $wishlist->save();
            $this->messageManager->addSuccessMessage(__('All wishlist item has been deleted.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t delete the item from Wish List right now because of an error: %1.', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t delete the item from the Wish List right now.'));
        }

        $this->_objectManager->get(\Magento\Wishlist\Helper\Data::class)->calculate();
        $refererUrl = $this->_redirect->getRefererUrl();
        if ($refererUrl) {
            $redirectUrl = $refererUrl;
        } else {
            $redirectUrl = $this->_redirect->getRedirectUrl($this->_url->getUrl('*/*'));
        }
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }
}
