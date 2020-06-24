<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/6/20
 * Time: 3:46 PM
 */
namespace Eguana\VideoBoard\Controller\Adminhtml\HowTo;

use Eguana\VideoBoard\Controller\Adminhtml\AbstractController;
use Eguana\VideoBoard\Model\VideoBoardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;

/**
 * This class is used to save the video record data
 *
 * Class Save
 * Eguana\VideoBoard\Controller\Adminhtml\HowTo
 */
class Save extends AbstractController
{
    /**
     * @var VideoBoardFactory
     */
    private $videoBoardFactory;

    /**
     * Save constructor.
     * @param Registry $coreRegistry
     * @param Context $context
     * @param VideoBoardFactory $videoBoardFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        VideoBoardFactory $videoBoardFactory
    ) {
        $this->videoBoardFactory = $videoBoardFactory;
        parent::__construct(
            $context,
            $coreRegistry,
            $resultPageFactory
        );
    }

    /**
     * execute action
     * This action is used to save the video record
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParam('video_information');
        if ($data) {
            if (isset($data['store_id'])) {
                $data['store_id'] = implode(',', $data['store_id']);
            }
            $model = $this->videoBoardFactory->create();
            if (isset($data['thumbnail_image'])) {
                $data['thumbnail_image'] = 'VideoBoard/' .
                    $data['thumbnail_image'][0]['file'];
            }
            $data['video_url'] = preg_replace(
                "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
                "https://www.youtube.com/embed/$2",$data['video_url']);

            $model->setUpdatedAt('');
            $model->setData($data)->save();
            $this->messageManager->addSuccess(__('Video has been successfully saved.'));
        } else {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('videoboard/howto/index');
    }

    /**
     * @param $model
     * @param $resultRedirect
     * @param $data
     * @return mixed
     */
    private function processResultRedirect($model, $resultRedirect, $data)
    {
        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            $newVideoBoard = $this->videoBoardFactory->create()->create(['data' => $data]);
            $newVideoBoard->setId(null);
            $identifier = $model->getUrlKey() . '-' . uniqid();
            $newVideoBoard->setUrlKey($identifier);
            $newVideoBoard->setIsActive(false);
            $newVideoBoard->setStoreId($model->getStoreId());
            $newVideoBoard->setThumbnailImage($model->getThumbnailImage());
            $this->videoBoardFactory->create()->save($newVideoBoard);
            $this->messageManager->addSuccessMessage(__('You duplicated the Video.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'entity_id' => $newVideoBoard->getId(),
                    '_current' => true
                ]
            );
        }
        if ($this->getRequest()->getParam('back', false) === 'continue') {
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId(), '_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
