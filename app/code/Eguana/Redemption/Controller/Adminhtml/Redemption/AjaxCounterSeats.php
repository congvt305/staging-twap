<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 14/1/21
 * Time: 10:45 AM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Controller\Adminhtml\Redemption;

use Eguana\Redemption\Block\Adminhtml\Redemption\CounterSeats\Form;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Used to show counter seats according to the counter
 *
 * Class AjaxCounterSeats
 */
class AjaxCounterSeats extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        $this->context = $context;
        $this->resultPageFactory = $pageFactory;
        parent::__construct($context);
    }
    /**
     * To get the counter seats list according to the selected counters
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $result = [
            'success' => false,
            'counterSeats' => ''
        ];
        $counterIds = $this->context->getRequest()->getParam('counterIds');
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if ($this->_request->isAjax() && $counterIds) {
            $resultPage = $this->resultPageFactory->create();
            $block = $resultPage->getLayout()
                ->createBlock(Form::class)
                ->toHtml();
            $result = [
                'success' => true,
                'counterSeats' => $block
            ];
        }
        $resultJson->setData($result);
        return $resultJson;
    }
}
