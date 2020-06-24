<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/18/20
 * Time: 6:29 AM
 */
namespace Eguana\Magazine\Controller\Detail;

use Eguana\Magazine\Api\MagazineRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * Controller to display detail about the magazine
 */

class Index extends Action
{
    /**
     * @var $magazine
     */
    private $magazine;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * Construct
     *
     * @param Context $context
     * @param View  $magazine
     */
    public function __construct(
        Context $context,
        MagazineRepositoryInterface $magazineRepository,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->magazineRepository = $magazineRepository;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\App\ResponseInterface|ResultInterfaceAlias|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $id ? $this->magazineRepository->getById($id) : null;

        if ($id) {
            if (!$model->getEntityId()) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedsirect;
        }
        return $this->resultPageFactory->create();
    }
}
