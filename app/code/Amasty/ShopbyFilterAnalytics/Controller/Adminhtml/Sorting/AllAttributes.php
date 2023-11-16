<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Controller\Adminhtml\Sorting;

use Amasty\ShopbyFilterAnalytics\Model\AttributeSorter;
use Amasty\ShopByQuickConfig\Block\MessageProcessor;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class AllAttributes extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Amasty_ShopByQuickConfig::navigation_attributes';

    /**
     * @var MessageProcessor
     */
    private $messageProcessor;

    /**
     * @var AttributeSorter
     */
    private $attributeSorter;

    public function __construct(
        Context $context,
        MessageProcessor $messageProcessor,
        AttributeSorter $attributeSorter
    ) {
        parent::__construct($context);
        $this->messageProcessor = $messageProcessor;
        $this->attributeSorter = $attributeSorter;
    }

    /**
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $filters = $this->_request->getParam('filters', []);

        try {
            $this->attributeSorter->sorAllAttributes($filters);
            $this->messageManager->addSuccessMessage(
                __('Filters have been successfully sorted based on analytics.')
            );
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        /** @var Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response['messages'] = $this->messageProcessor->getMessagesArray();
        $result->setData($response);

        return $result;
    }
}
