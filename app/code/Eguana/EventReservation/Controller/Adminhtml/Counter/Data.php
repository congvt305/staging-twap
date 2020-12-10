<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 4/11/20
 * Time: 3:38 PM
 */
namespace Eguana\EventReservation\Controller\Adminhtml\Counter;

use Eguana\EventReservation\Block\Adminhtml\Counter\Edit\Main;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Data
 *
 * Data provide class
 */
class Data extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultPageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Load form output
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $result = $this->resultJsonFactory->create();
        $block = $resultPage->getLayout()
            ->createBlock(Main::class)
            ->toHtml();
        $result->setData(['output' => $block]);

        return $result;
    }
}
