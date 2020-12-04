<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: shahroz
 * Date: 10/12/19
 * Time: 10:54 AM
 */
namespace Eguana\CustomerBulletin\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Eguana\CustomerBulletin\Model\TicketCloser;

/**
 * This class is responsible for run cron
 * Class Run
 */
class Run extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var TicketCloser
     */
    private $ticketCloser;

    /**
     * Run constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param TicketCloser $ticketCloser
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        TicketCloser $ticketCloser
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
        $this->ticketCloser = $ticketCloser;
    }

    /**
     * Collect relations data
     *
     * @return Json
     */
    public function execute()
    {
        $this->ticketCloser->closeTicket();
        $result = $this->resultJsonFactory->create();
        return $result->setData(['success' => true]);
    }
}
