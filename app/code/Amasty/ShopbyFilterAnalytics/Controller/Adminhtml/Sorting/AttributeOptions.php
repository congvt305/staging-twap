<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Controller\Adminhtml\Sorting;

use Amasty\ShopbyFilterAnalytics\Model\OptionsSorter;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

class AttributeOptions extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Amasty_ShopByQuickConfig::navigation_attributes';

    /**
     * @var OptionsSorter
     */
    private $optionsSorter;

    public function __construct(
        Context $context,
        OptionsSorter $optionsSorter
    ) {
        parent::__construct($context);
        $this->optionsSorter = $optionsSorter;
    }

    /**
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $filters = $this->_request->getParam('filters', []);
        $selected = $this->_request->getParam(Filter::SELECTED_PARAM);
        $excluded = $this->_request->getParam(Filter::EXCLUDED_PARAM);

        if ($excluded !== 'false' && !$this->isFilterParamValid($excluded) && !$this->isFilterParamValid($selected)) {
            $this->messageManager->addErrorMessage(__('An item needs to be selected. Select and try again.'));
        } else {
            try {
                $this->optionsSorter->sortOptionsOfAttributes(
                    $this->prepareSelection($selected),
                    $this->prepareSelection($excluded),
                    $filters
                );
                $this->messageManager->addSuccessMessage(
                    __('Options have been successfully sorted based on analytics.')
                );
            } catch (LocalizedException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('amasty_shopbyconfig/filters/index');
    }

    /**
     * @param mixed $param
     *
     * @return array
     */
    private function prepareSelection($param): array
    {
        if (!$this->isFilterParamValid($param)) {
            return [];
        }

        return array_map('intval', $param);
    }

    /**
     * @param mixed $param
     *
     * @return bool
     */
    private function isFilterParamValid($param): bool
    {
        return (is_array($param) && !empty($param));
    }
}
