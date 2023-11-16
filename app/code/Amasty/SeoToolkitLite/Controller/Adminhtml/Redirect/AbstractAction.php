<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Controller\Adminhtml\Redirect;

use Amasty\SeoToolkitLite\Api\Data\RedirectInterfaceFactory;
use Amasty\SeoToolkitLite\Api\RedirectRepositoryInterface;
use Amasty\SeoToolkitLite\Model\Redirect\TargetPathValidator;
use Amasty\SeoToolkitLite\Model\ResourceModel\Redirect as RedirectResource;
use Amasty\SeoToolkitLite\Model\ResourceModel\Redirect\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface as Logger;
use Magento\Backend\Model\View\Result\ForwardFactory;

abstract class AbstractAction extends Action
{
    public const ADMIN_RESOURCE = 'Amasty_SeoToolkitLite::redirect_management';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var RedirectRepositoryInterface
     */
    protected $redirectRepository;

    /**
     * @var RedirectResource
     */
    protected $redirectResource;

    /**
     * @var RedirectInterfaceFactory
     */
    protected $redirectFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var TargetPathValidator
     */
    protected $targetPathValidator;

    public function __construct(
        Context $context,
        Filter $filter,
        ForwardFactory $resultForwardFactory,
        RedirectRepositoryInterface $redirectRepository,
        CollectionFactory $collectionFactory,
        RedirectInterfaceFactory $redirectFactory,
        RedirectResource $redirectResource,
        Logger $logger,
        TargetPathValidator $targetPathValidator
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->redirectRepository = $redirectRepository;
        $this->collectionFactory = $collectionFactory;
        $this->redirectResource = $redirectResource;
        $this->redirectFactory = $redirectFactory;
        $this->redirectResource = $redirectResource;
        $this->logger = $logger;
        $this->targetPathValidator = $targetPathValidator;
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Amasty_SeoToolkitLite::redirects');
        $resultPage->getConfig()->getTitle()->prepend(__('Redirects'));

        return $resultPage;
    }
}
