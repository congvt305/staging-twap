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
use Eguana\VideoBoard\Model\VideoBoard;
use Eguana\VideoBoard\Model\VideoBoardFactory;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Backend\App\Action;
use Eguana\VideoBoard\Api\VideoBoardRepositoryInterface;

/**
 * This class is used to save the video record data
 *
 * Class Save
 */
class Save extends AbstractController
{
    /**
     * Constant
     */
    const URL_PATTERN = '/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var VideoBoardFactory
     */
    private $videoBoardFactory;

    /**
     * @var VideoBoardRepositoryInterface
     */
    private $videoBoardRepository;

    /**
     * Save constructor.
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param VideoBoardFactory $videoBoardFactory
     * @param VideoBoardRepositoryInterface $videoBoardRepository
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        PageFactory $resultPageFactory,
        VideoBoardFactory $videoBoardFactory,
        VideoBoardRepositoryInterface $videoBoardRepository
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->videoBoardFactory = $videoBoardFactory;
        $this->videoBoardRepository = $videoBoardRepository;
        parent::__construct(
            $context,
            $resultPageFactory
        );
    }

    /**
     * execute action
     * This action is used to save the video record
     * @return ResponseInterfaceAlias|ResultInterfaceAlias|void
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $generalData = $data;
            if (isset($generalData['active']) && $generalData['active'] === '1') {
                $generalData['is_active'] = 1;
            }
            if (empty($generalData['entity_id'])) {
                $generalData['entity_id'] = null;
            }
            $id = $generalData['entity_id'];
            /** @var VideoBoard $model */
            $model = $this->videoBoardFactory->create();
            if ($id) {
                try {
                    $model = $this->videoBoardRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager
                        ->addErrorMessage(__('This video no longer exists.'));
                    return $this->processResultRedirect($model, $resultRedirect, $data);
                }
            }
            if (isset($generalData['thumbnail_image'])) {
                $generalData['thumbnail_image'] = 'VideoBoard/' .
                    $generalData['thumbnail_image'][0]['file'];
            }
            $generalData['video_url'] = preg_replace(
                self::URL_PATTERN,
                "https://www.youtube.com/embed/$2",
                $generalData['video_url']
            );
            $model->setData($generalData);
            try {
                $model->setUpdatedAt('');
                $this->videoBoardRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the video.'));
                return $this->processResultRedirect($model, $resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the video.')
                );
            }
            $this->dataPersistor->set('eguana_video_board', $data);
            return $this->processResultRedirect($model, $resultRedirect, $data);
        }
        return $this->processResultRedirect($model, $resultRedirect, $data);
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
            $newVideoBoard = $this->videoBoardFactory->create(['data' => $data]);
            $newVideoBoard->setId(null);
            $newVideoBoard->setIsActive(false);
            $newVideoBoard->setThumbnailImage($model->getThumbnailImage());
            $this->videoBoardRepository->save($newVideoBoard);
            $this->messageManager->addSuccessMessage(__('You duplicated the Video.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'entity_id' => $newVideoBoard->getId(),
                    '_current' => true
                ]
            );
        }
        $this->dataPersistor->clear('eguana_video_board');
        if ($this->getRequest()->getParam('back', false) === 'continue') {
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId(), '_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
