<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 16/6/20
 * Time: 6:26 PM
 */
namespace Eguana\VideoBoard\Controller\Detail;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Eguana\VideoBoard\Api\VideoBoardRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 * Controller to display details about the video
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var VideoBoardRepositoryInterface
     */
    private $videoBoardRepository;

    /**
     * @var ResultFactory
     */
    private $result;

    /**
     * @var ManagerInterface
     */
    private $managerInterface;

    /**
     * Construct
     *
     * @param Context $context
     * @param View  $videoBoard
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        VideoBoardRepositoryInterface $videoBoardRepository,
        ResultFactory $result,
        ManagerInterface $managerInterface
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->videoBoardRepository = $videoBoardRepository;
        $this->result = $result;
        $this->managerInterface = $managerInterface;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $videoId = $this->_request->getParam('id');
        if (isset($videoId)) {
            $video = $this->videoBoardRepository->getById($videoId);
            if (empty($video->getData())) {
                $this->managerInterface->addErrorMessage('No video exist with ' .$videoId . ' id');
                $resultRedirect = $this->result->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl('/videoboard');
                return $resultRedirect;
            }
        } elseif (!isset($videoId)) {
            $resultRedirect = $this->result->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('/videoboard');
            return $resultRedirect;
        }
        return $this->resultPageFactory->create();
    }
}
