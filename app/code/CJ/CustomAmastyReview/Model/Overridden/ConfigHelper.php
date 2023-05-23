<?php

namespace CJ\CustomAmastyReview\Model\Overridden;

use Magento\Review\Model\ReviewFactory;

/**
 * Class ConfigHelper
 */
class ConfigHelper extends \Amasty\AdvancedReview\Helper\Config
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    private $imageFactory;

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $fileManager;
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var array
     */
    private $correctSortOrder;

    /**
     * @param \Amasty\Base\Model\Serializer $serializer
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param ReviewFactory $reviewFactory
     * @param \Magento\Framework\Filesystem\Io\File $fileManager
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        ReviewFactory $reviewFactory,
        \Magento\Framework\Filesystem\Io\File $fileManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Framework\Filter\FilterManager $filterManager)
    {
        parent::__construct($serializer, $context, $storeManager, $filesystem, $imageFactory, $reviewFactory, $fileManager, $jsonEncoder, $sessionFactory, $filterManager);
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->reviewFactory = $reviewFactory;
        $this->fileManager = $fileManager;
        $this->jsonEncoder = $jsonEncoder;
        $this->sessionFactory = $sessionFactory;
        $this->filterManager = $filterManager;
        $this->serializer = $serializer;
        $this->correctSortOrder = array_keys($this->getSortOrder());
    }

    /**
     * @return mixed
     */
    private function getCustomerSession()
    {
        return $this->sessionFactory->create();
    }

    /**
     * @param array $options
     * @return array
     */
    private function arrayFlip($options = [])
    {
        if (isset(array_values($options)[0]) && !is_object(array_values($options)[0])) {
            $options = array_flip($options);
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getSortingOptions()
    {
        $sort = $this->getModuleConfig('general/sort_by');
        if ($sort) {
            $sort = explode(',', $sort??'');
        } else {
            $sort = [];
        }

        return $this->sortOptions($sort);
    }

    /**
     * @return array
     */
    public function getFilteringOptions()
    {
        $filter = $this->getModuleConfig('general/filter_by');
        if ($filter) {
            $filter = explode(',', $filter??'');
        } else {
            $filter = [];
        }

        return $filter;
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getTriggerOrderStatus(?int $storeId = null): array
    {
        if (!($statuses = $this->getModuleConfig('reminder/order_status', $storeId))) {
            return [];
        }

        return explode(',', $statuses??'');
    }

    /**
     * @return array
     */
    public function getAdminNotificationEmails()
    {
        $emails = $this->getModuleConfig('admin_notifications/email');
        $emails = $emails ? explode(',', $emails??'') : [];

        return $emails;
    }

    /**
     * @return array
     */
    public function getCustomerGroups()
    {
        $groups = $this->getModuleConfig('coupons/customer_group');

        return explode(',', $groups??'');
    }

    /**
     * @return array
     */
    public function getReminderGroups(): array
    {
        $groups = $this->getModuleConfig('reminder/customer_group');
        if ($groups) {
            return explode(',', (string) $groups??'');
        }

        return [];
    }

    /**
     * @return array
     */
    public function getReminderEmails()
    {
        $emails = $this->getModuleConfig('reminder/reminder_emails');
        $emails = $emails ? explode(',', $emails??'') : [];

        return $emails;
    }
}
