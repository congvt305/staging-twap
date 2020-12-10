<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 16/10/20
 * Time: 12:00 PM
 */
namespace Eguana\EventReservation\Controller\Adminhtml\Reservation;

use Eguana\EventReservation\Model\ResourceModel\Event\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * To delete multiple events
 *
 * Class MassDelete
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param UrlPersistInterface $urlPersist
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Filter $filter,
        Context $context,
        UrlPersistInterface $urlPersist,
        CollectionFactory $collectionFactory
    ) {
        $this->filter               = $filter;
        $this->urlPersist           = $urlPersist;
        $this->collectionFactory    = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action to delete events
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();

            foreach ($collection as $event) {
                $event->delete();

                $this->urlPersist->deleteByData([
                    UrlRewrite::ENTITY_ID => $event->getId(),
                    UrlRewrite::ENTITY_TYPE => 'custom',
                    UrlRewrite::REDIRECT_TYPE => 0,
                    UrlRewrite::TARGET_PATH => 'event/reservation/index/id/' . $event->getId()
                ]);
            }

            $this->messageManager->addSuccessMessage(
                __('A total of %1 event(s) have been deleted.', $collectionSize)
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
