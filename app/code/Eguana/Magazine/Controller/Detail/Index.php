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
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\Message\ManagerInterface;
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
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var ManagerInterface
     */
    private $managerInterface;

    /**
     * Construct
     *
     * @param Context $context
     * @param View  $magazine
     */
    public function __construct(
        Context $context,
        MagazineRepositoryInterface $magazineRepository,
        PageFactory $resultPageFactory,
        ResultFactory $resultFactory,
        ManagerInterface $managerInterface
    ) {
        parent::__construct($context);
        $this->magazineRepository = $magazineRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $resultFactory;
        $this->managerInterface = $managerInterface;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\App\ResponseInterface|ResultInterfaceAlias|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $magazine = $this->magazineRepository->getById($id);

        if (isset($id)) {
            if (empty($magazine)) {
                $this->managerInterface->addErrorMessage('No magazine exist with this ' . $id . 'id');
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }
        } elseif (!isset($id)) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        return $this->resultPageFactory->create();
    }
}
