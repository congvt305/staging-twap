<?php

namespace CJ\EventManager\Helper;

use Eguana\EventManager\Model\EventManagerFactory;
use Eguana\EventManager\Model\ResourceModel\EventManager\CollectionFactory as EventManagerCollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class Data extends AbstractHelper
{
    /**
     * @var EventManagerCollectionFactory
     */
    protected $eventManagerCollectionFactory;
    /**
     * @var EventManagerFactory
     */
    protected $eventManagerFactory;
    /**
     * @var PsrLoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param EventManagerCollectionFactory $eventManagerCollectionFactory
     * @param EventManagerFactory $eventManagerFactory
     * @param PsrLoggerInterface $logger
     */
    public function __construct(
        Context $context,
        EventManagerCollectionFactory $eventManagerCollectionFactory,
        EventManagerFactory $eventManagerFactory,
        PsrLoggerInterface $logger
    ){
        parent::__construct($context);
        $this->eventManagerCollectionFactory = $eventManagerCollectionFactory;
        $this->eventManagerFactory = $eventManagerFactory;
        $this->logger = $logger;
    }

    /**
     * @param $fromStoreId
     * @param $toStoreId
     * @return void
     */
    public function migrateEvents($fromStoreId, $toStoreId)
    {
        $this->logger->info('============ Migration Events ==============');
        $items = $this->eventManagerCollectionFactory->create();
        $items->addStoreFilter($fromStoreId, false);
        $this->logger->info(__('Number Events clone %1', $items->getSize()));
        $tmp = 0;
        foreach ($items as $item){
            /**
             * @var \Eguana\EventManager\Model\EventManager $item
             */
            $data = [
                'event_title' => $item->getTitle(),
                'description' => $item->getDescription(),
                'thumbnail_image' => $item->getThumbnailImage(),
                'is_active' => $item->getIsActive(),
                'start_date' => $item->getStartDate(),
                'end_date' => $item->getEndDate(),
                'store_id' => [$toStoreId]
            ];
            try {
                $this->createEventManager()->setData($data)->save();
                $tmp++;
            }catch (\Exception $exception){
                $this->logger->info(__('ID Event Clone Fail: %1', $item->getId()));
                $this->logger->info(__('Error Message : %1', $exception->getMessage()));
                continue;
            }
        }
        $this->logger->info(__('Number Events clone completed %1', $tmp));
    }

    public function createEventManager()
    {
        return $this->eventManagerFactory->create();
    }
}
