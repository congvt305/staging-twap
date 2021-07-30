<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 11/3/21
 * Time: 8:12 PM
 */
namespace Eguana\Redemption\Controller\Details;

use Eguana\Redemption\Api\RedemptionRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;

/**
 * To show success page after registration
 *
 * Class Success
 */
class Success extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var RedemptionRepositoryInterface
     */
    private $redemptionRepository;

    /**
     * @param Context $context
     * @param RedemptionRepositoryInterface $redemptionRepository
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        RedemptionRepositoryInterface $redemptionRepository
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->redemptionRepository = $redemptionRepository;
    }

    /**
     * Used to load layout and render information
     *
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        $redemptionId = $this->_request->getParam('redemption_id');
        if ($redemptionId) {
            $redemption = $this->redemptionRepository->getById($redemptionId);
            if(!$redemption->getId()) {
                $this->messageManager->addErrorMessage(__('This redemption no longer exists.'));
                return $redirect->setUrl('/');
            }
            return $this->pageFactory->create();
        } else {
            return $redirect->setUrl('/');
        }
    }
}
