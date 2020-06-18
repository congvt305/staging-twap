<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/18/20
 * Time: 6:29 AM
 */

namespace Eguana\Magazine\Controller\Details;

use Eguana\Magazine\Block\View;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

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
     * Construct
     *
     * @param Context $context
     * @param View  $magazine
     */
    public function __construct(
        Context $context,
        \Eguana\Magazine\Api\MagazineRepositoryInterface $magazineRepository,
        View $magazine
    ) {
        parent::__construct($context);
        $this->magazineRepository = $magazineRepository;
        $this->magazine = $magazine;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {

        $id = $this->getRequest()->getParam('id');
        $model = $id ? $this->magazineRepository->getById($id) : null;

        // 2. Initial checking
        if ($id) {
            if (!$model->getEntityId()) {
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;

            }
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
