<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/10/20
 * Time: 12:11 PM
 */
namespace Eguana\Redemption\Controller\Details;

use Eguana\Redemption\Api\CounterRepositoryInterface;
use Eguana\Redemption\Model\Counter;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * This class is used to create page
 * Class Register
 */
class Register extends Action
{
    /**
     * @var Counter
     */
    private $counter;

    /**
     * @var ResultFactory
     */
    private $result;

    /**
     * @var CounterRepositoryInterface
     */
    private $counterRepository;

    /**
     * @var ManagerInterface
     */
    private $managerInterface;

    /**
     * Register constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Counter $counter
     * @param ResultFactory $result
     * @param CounterRepositoryInterface $counterRepository
     * @param ManagerInterface $managerInterface
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Counter $counter,
        ResultFactory $result,
        CounterRepositoryInterface $counterRepository,
        ManagerInterface $managerInterface
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->counter = $counter;
        $this->result = $result;
        $this->counterRepository = $counterRepository;
        $this->managerInterface = $managerInterface;
    }

    /**
     * This method is used to create page to show and update the Confirm Message
     *
     * @return ResponseInterface|ResultInterface|Page
     * @throws \Exception
     */
    public function execute()
    {
        $counterId = $this->getRequest()->getParam('counter_id');
        $token = $this->getRequest()->getParam('token');
        if (isset($counterId)) {
            $counter = $this->counterRepository->getById($counterId);
            if (empty($counter->getData())) {
                $this->managerInterface->addErrorMessage(__('No counter exsit with ' . $counterId . ' id'));
                $resultRedirect = $this->result->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl('/');
                return $resultRedirect;
            }
        }
        if ($token === $this->counterRepository->getById($counterId)->getToken()) {
            return $this->resultPageFactory->create();
        } else {
            $this->messageManager->addErrorMessage(__('You are not authorized for this redemption.'));
            $resultRedirect = $this->result->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setUrl('/');
        }
        return $this->resultPageFactory->create();
    }
}
