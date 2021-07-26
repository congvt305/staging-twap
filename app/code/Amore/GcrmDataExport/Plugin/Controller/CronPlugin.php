<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 13/7/21
 * Time: 3:54 PM
 */
namespace Amore\GcrmDataExport\Plugin\Controller;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\ScheduledImportExport\Controller\Adminhtml\Scheduled\Operation\Cron;

/**
 * That class responsible to show error messages
 * Class AddPlugin
 */
class CronPlugin
{
    /**
     * @var RedirectInterface
     */
    private $redirectInterface;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * AddPlugin constructor.
     * @param RedirectInterface $redirectInterface
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        RedirectInterface $redirectInterface,
        ManagerInterface $messageManager,
        DataPersistorInterface $dataPersistor
    ) {
        $this->redirectInterface = $redirectInterface;
        $this->messageManager = $messageManager;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * This function sets error message
     * @param Redirect $resultRedirect
     */
    public function afterExecute(Cron $subject, Redirect $resultRedirect) {
        $dataPersistorResult = $this->dataPersistor->get('operation_status');
        if (!$dataPersistorResult) {
            $this->messageManager->getMessages(true);
            $this->messageManager->addError(__('There is no data For Export.'));
        }
        return $resultRedirect;
    }
}
