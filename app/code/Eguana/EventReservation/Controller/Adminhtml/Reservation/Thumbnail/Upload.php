<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 16/10/20
 * Time: 2:40 PM
 */
namespace Eguana\EventReservation\Controller\Adminhtml\Reservation\Thumbnail;

use Eguana\EventReservation\Model\Event\Thumbnail\Uploader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Used to upload the event thumbnail
 *
 * Class Upload
 */
class Upload extends Action implements HttpPostActionInterface
{
    /**#@+
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Eguana_EventReservation::event_reservation';
    /**#@-*/

    /**
     * @var Uploader
     */
    private $uploader;

    /**
     * @param Context $context
     * @param Uploader $uploader
     */
    public function __construct(
        Context $context,
        Uploader $uploader
    ) {
        $this->uploader = $uploader;
        parent::__construct($context);
    }

    /**
     * Upload file controller action
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $thumbnail = $this->_request->getParam('param_name', 'thumbnail');
        try {
            $result = $this->uploader->saveFileToTmpDir($thumbnail);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
