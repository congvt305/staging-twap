<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 8/10/20
 * Time: 3:53 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\ViewModel;

use Eguana\CustomerBulletin\Helper\Data;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This class is used for the customer create ticket page data
 *
 * Class CreateTicket
 */
class CreateTicket implements ArgumentInterface
{
    /**#@+
     * Constant for icon path
     */
    const FILE_ATTACHEMNT_ICON_STW = 'Eguana_CustomerBulletin::images/s-paper-clip.svg';
    const FILE_ATTACHEMNT_ICON = 'Eguana_CustomerBulletin::images/l-paper-clip.svg';
    /**#@-*/

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Json
     */
    private $json;

    /**
     * CreateTicket constructor.
     * @param Json $json
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param Data $helperData
     * @param LoggerInterface $logger
     */
    public function __construct(
        Json $json,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        Data $helperData,
        LoggerInterface $logger
    ) {
        $this->json = $json;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
        $this->logger = $logger;
    }

    /**
     * get Category values from configuration
     *
     * @return mixed
     */
    public function getCategory()
    {
        $result = $this->helperData->getGeneralConfig('configuration/category');
        $categories = $this->json->unserialize($result);
        $category = [];
        foreach ($categories as $value) {
            $category[] = $value['attribute_name'];
        }
        return $category;
    }

    /**
     * get Email values from configuration
     *
     * @return mixed
     */
    public function getEmailSender()
    {
        return $this->helperData->getGeneralConfig('configuration/sender_email_identity');
    }

    /**
     * get Email of Sender from configuration
     *
     * @return mixed
     */
    public function getEmail()
    {
        return $this->helperData->getEmail('trans_email/ident_' . $this->getEmailSender() . '/email');
    }

    /**
     * get Url of fiel attachment icon
     *
     * @return string
     */
    public function getFileIconSwtUrl() : string
    {
        return self::FILE_ATTACHEMNT_ICON_STW;
    }

    /**
     * get Url of fiel attachment icon
     *
     * @return string
     */
    public function getFileIconUrl() : string
    {
        return self::FILE_ATTACHEMNT_ICON;
    }

    /**
     * get website code
     *
     * @return string
     */
    public function getWebsiteCode()
    {
         $websiteCode = "";
        try {
            $websiteCode = $this->storeManager->getWebsite()->getCode();
            return $websiteCode;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $websiteCode;
    }
}
