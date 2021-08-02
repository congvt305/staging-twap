<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 7/29/21
 * Time: 5:55 AM
 */

namespace Amore\GcrmDataExport\Observer\Customer;

use Amore\GcrmDataExport\Model\Config\Config;
use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Amore\CustomerRegistration\Model\POSLogger;

/**
 * This class saves data of customer bulletin tickets into Heroku DB
 *
 * Class SaveBulletin
 */
class SaveBulletin implements ObserverInterface
{
    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * @var POSLogger
     */
    private $logger;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Config
     */
    private $configData;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param Config $configData
     * @param TicketRepositoryInterface $ticketRepository
     * @param ManagerInterface $messageManager
     * @param POSLogger $logger
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Config $configData,
        TicketRepositoryInterface $ticketRepository,
        ManagerInterface $messageManager,
        POSLogger $logger,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->configData = $configData;
        $this->ticketRepository = $ticketRepository;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Observer Execute function to insert data into heroku DB
     *
     * @param Observer $observer
     */
    public function execute(
        Observer $observer
    ) {
        try {
            $ticketID = $observer->getEvent()->getData('ticketID');
            if ($ticketID) {
                $ticketData = $this->ticketRepository->getById($ticketID);
                if ($ticketData) {
                    $ticketNumber = $ticketID;
                    $customerID = $ticketData->getCustomerId();
                    $customerData = $this->customerRepository->getById($customerID);
                    $customerIntegNo = $customerData->getCustomAttribute('integration_number')->getValue();
                    $ticketType = $ticketData->getCategory();
                    $ticketTitle = $ticketData->getSubject();
                    $ticketContent = $ticketData->getMassege();
                    $createdAt = $ticketData->getCreationTime();
                    $updatedAt = $ticketData->getUpdateTime();
                    $attachmentURL = $ticketData->getAttachment();

                    $host = $this->configData->getHerokuHost();
                    $dbname = $this->configData->getHerokuDBName();
                    $user = $this->configData->getHerokuUser();
                    $password = $this->configData->getHerokuPassword();
                    $db_connection = pg_connect(
                        "host=$host
                         dbname=$dbname
                         user=$user
                         password=$password"
                    );
                    if ($db_connection) {
                        $sql = "INSERT INTO apgcrm.GECPCase__c (counselno__c,cstmintgseq__c,counseltypecd__c,counselsbj__c,
                                counselcnt__c,regdate__c,upddate__c,attachmenturl__c) VALUES ('$ticketNumber',
                                '$customerIntegNo','$ticketType','$ticketTitle','$ticketContent',
                                '$createdAt','$updatedAt','$attachmentURL')";
                        $result = pg_exec($db_connection, $sql);
                        pg_close($db_connection);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->addExceptionMessage($e->getMessage());
        }
    }
}
