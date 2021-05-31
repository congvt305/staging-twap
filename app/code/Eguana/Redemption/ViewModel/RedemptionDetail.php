<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 22/10/20
 * Time: 5:00 PM
 */
namespace Eguana\Redemption\ViewModel;

use Eguana\Redemption\Api\CounterRepositoryInterface;
use Eguana\Redemption\Api\RedemptionRepositoryInterface;
use Eguana\StoreLocator\Api\StoreInfoRepositoryInterface;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo\CollectionFactory;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;
use Eguana\Redemption\Model\RedemptionConfiguration\RedemptionConfiguration;
use Psr\Log\LoggerInterface;

/**
 * This ViewModel is used to show single Redemption detail
 *
 * Class RedemptionDetail
 */
class RedemptionDetail implements ArgumentInterface
{
    /**
     * Constant
     */
    const DATE_FORMAT = 'Y-m-d';

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RedemptionRepositoryInterface
     */
    private $redemptionRepository;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepositoryInterface;

    /**
     * @var StoreInfoRepositoryInterface
     */
    private $storeInfoRepositoryInterface;

    /**
     * @var CounterRepositoryInterface
     */
    private $counterRepository;

    /**
     * @var CollectionFactory
     */
    private $storeInfoCollectionFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var RedemptionConfiguration
     */
    private $redemptionConfiguration;

    /**
     * Redemption constructor.
     *
     * @param Http $request
     * @param FilterProvider $filterProvider
     * @param RedemptionRepositoryInterface $redemptionRepository
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     * @param BlockRepositoryInterface $blockRepositoryInterface
     * @param StoreInfoRepositoryInterface $storeInfoRepositoryInterface
     * @param CounterRepositoryInterface $counterRepository
     * @param CollectionFactory $storeInfoCollectionFactory
     * @param ManagerInterface $messageManager
     * @param RedemptionConfiguration $redemptionConfiguration
     * @param array $data
     */
    public function __construct(
        Http $request,
        FilterProvider $filterProvider,
        RedemptionRepositoryInterface $redemptionRepository,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timezone,
        LoggerInterface $logger,
        BlockRepositoryInterface $blockRepositoryInterface,
        StoreInfoRepositoryInterface $storeInfoRepositoryInterface,
        CounterRepositoryInterface $counterRepository,
        CollectionFactory $storeInfoCollectionFactory,
        ManagerInterface $messageManager,
        RedemptionConfiguration $redemptionConfiguration,
        array $data = []
    ) {
        $this->request = $request;
        $this->filterProvider = $filterProvider;
        $this->storeManager = $storeManager;
        $this->redemptionRepository = $redemptionRepository;
        $this->timezone = $timezone;
        $this->blockRepositoryInterface = $blockRepositoryInterface;
        $this->logger = $logger;
        $this->storeInfoRepositoryInterface = $storeInfoRepositoryInterface;
        $this->counterRepository = $counterRepository;
        $this->storeInfoCollectionFactory = $storeInfoCollectionFactory;
        $this->messageManager = $messageManager;
        $this->redemptionConfiguration = $redemptionConfiguration;
    }

    /**
     * Get Redemption id
     *
     * @return mixed
     */
    private function getRedemptionId()
    {
        return $this->request->getParam('redemption_id');
    }

    /**
     * Get Utm Source
     *
     * @return mixed
     */
    public function getUtmSource()
    {
        $this->logger->debug('utm source');
        $this->logger->debug(json_encode($this->request->getParams()));
        return $this->request->getParam('utm_source');
    }

    /**
     * Get Utm Medium
     *
     * @return mixed
     */
    public function getUtmMedium()
    {
        $this->logger->debug('utm source');
        $this->logger->debug(json_encode($this->request->getParams(), true));
        return $this->request->getParam('utm_medium');
    }

    /**
     * Get Utm Content
     *
     * @return mixed
     */
    public function getUtmContent()
    {
        $this->logger->debug('utm source');
        $this->logger->debug(json_encode($this->request->getParams(), true));
        return $this->request->getParam('utm_content');
    }

    /**
     * get Redemption method
     *
     * @return Redemption
     */
    public function getRedemption()
    {
        /** @var Redemption $redemption */
        $redemption = $this->redemptionRepository->getById($this->getRedemptionId());
        return $redemption;
    }

    /**
     * This method is used to get the redemption ID
     * @param $id
     * @return Redemption
     */
    public function getRedemptionById($id)
    {
        /** @var Redemption $redemption */
        $redemption = $this->redemptionRepository->getById($id);
        return $redemption;
    }

    /**
     * get Counter Name method
     *
     * @param $id
     * @return Redemption
     */
    public function getCounterName($id)
    {
        $counterDetail = $this->storeInfoRepositoryInterface->getById($id);
        $counterTitle = $counterDetail->getTitle();
        return $counterTitle;
    }

    /**
     * It will return the thumbanil image URL
     *
     * @param $file
     * @return string
     */
    public function getThumbnailImageURL($file)
    {
        if ($file == '') {
            return '';
        }
        return $this->getMediaUrl($file);
    }

    /**
     * Get file url
     *
     * @param $file
     * @return string
     */
    public function getMediaUrl($file)
    {
        $result = '';
        try {
            $file = ltrim(str_replace('\\', '/', $file), '/');
            $result = $this->storeManager
                    ->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $file;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $result;
    }

    /**
     * get Cms Block Identifier method
     *
     * @param $id
     * @return Redemption
     * @throws LocalizedException
     */
    public function getCmsBlockIdentifier($id)
    {
        $block = $this->blockRepositoryInterface->getById($id);
        return $blockIdentifier = $block->getIdentifier();
    }

    /**
     * To filter the content
     * This function will get the content, specially the page builder content and make it renderable at frontend.
     *
     * @param $content
     * @return mixed
     */
    public function contentFiltering($content)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter($content);
    }

    /**
     * get store id
     * @return int
     */
    public function getStoreId()
    {
        $result = '';
        try {
            $result = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $result;
    }

    /**
     * This method is used to change the date format
     * @param $date
     * @return string
     */
    public function changeDateFormat($date)
    {
        $result = '';
        try {
            $result = $this->timezone->date($date)->format(self::DATE_FORMAT);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $result;
    }

    /**
     * Get form action URL for POST Counter request
     *
     * @return string
     */
    public function getFormAction() : string
    {
        $formActionUrl = '';
        try {
            $formActionUrl = $this->storeManager->getStore()->getUrl('redemption/details/index/');
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $formActionUrl;
    }

    /**
     * Get form action URL for resend email and sms
     *
     * @return string
     */
    public function getResendAction() : string
    {
        $resendFormActionUrl = '';
        try {
            $resendFormActionUrl =  $this->storeManager
                ->getStore()
                ->getUrl('redemption/details/resend/', ['counter_id'=>$this->request->getParam('counter_id')]);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $resendFormActionUrl;
    }

    /**
     * Get Counter id
     *
     * @return mixed
     */
    private function getCounterId()
    {
        return $this->request->getParam('counter_id');
    }

    /**
     * This method is used to get the counter details
     *
     * @return Counter
     */
    public function getCounterDetail()
    {
        /** @var Counter $counter */
        $counter = $this->counterRepository->getById($this->getCounterId());
        if (empty($counter->getdata())) {
            $this->messageManager->addErrorMessage(__('Counter information not found'));
            return $counter = "";
        }
        return $counter;
    }

    /**
     * This method is used to check if the current counter exists in this store
     *
     * @param $counterId
     * @return bool
     */
    public function checkStoreLocatorInWebsite($counterId)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $storeCollection = $this->storeInfoCollectionFactory->create();
            $storeCollection->addFieldToFilter(
                "entity_id",
                ["eq" => $counterId]
            );
            $storeCount = $storeCollection->addStoreFilter($storeId)->count();
            if ($storeCount > 0) {
                return true;
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return false;
    }

    /**
     * This method is used to get the time after which the resend email and sms button enable
     *
     * @return string
     */
    public function getTimeForResendEmailButton()
    {
        return $this->redemptionConfiguration->getTimeForResendEmailButton($this->getStoreId());
    }

    /**
     * This method is used to get the new redemption URL
     *
     * @param $identifier
     * @return string
     */
    public function getNewRedemptionUrl($identifier)
    {
        $newRedemptionUrl = '';
        try {
            $newRedemptionUrl = $this->storeManager->getStore()->getUrl($identifier);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $newRedemptionUrl;
    }

    /**
     * This method is used to get counter save url
     *
     * @return string
     */
    public function getCounterSaveUrl()
    {
        $counterSaveUrl = '';
        try {
            $counterSaveUrl =  $this->storeManager
                ->getStore()
                ->getUrl('redemption/details/counterSaveAjax/');
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $counterSaveUrl;
    }

    /**
     * This method is used to get the minimum mobile number digits
     *
     * @return string
     */
    public function getMinimumMobileNumberDigits()
    {
        return $this->redemptionConfiguration->getMinimumMobileNumberDigits($this->getStoreId());
    }

    /**
     * This method is used to get the maximum mobile number digits
     *
     * @return string
     */
    public function getMaximumMobileNumberDigits()
    {
        return $this->redemptionConfiguration->getMaximumMobileNumberDigits($this->getStoreId());
    }

    /**
     * Get privacy policy text
     *
     * @return string
     */
    public function getPrivacyPolicy()
    {
        return $this->redemptionConfiguration->getPrivacyPolicy($this->getStoreId());
    }

    /**
     * Get success page action URL
     *
     * @return string
     */
    public function getSuccessPageUrl() : string
    {
        $url = '';
        try {
            $url =  $this->storeManager
                ->getStore()
                ->getUrl(
                    'redemption/details/success/',
                    ['redemption_id' => $this->getRedemptionId()]
                );
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $url;
    }
}
