<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 4/9/20
 * Time: 5:02 PM
 */
namespace Eguana\NameSorter\Plugin\Helper;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View as ViewAlias;
use Psr\Log\LoggerInterface;

/**
 * This class consists of After plugin which changes the order of First and Last Name
 * Class View
 */
class View
{
    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadataService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * View constructor.
     * @param CustomerMetadataInterface $customerMetadataService
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerMetadataInterface $customerMetadataService,
        LoggerInterface $logger
    ) {
        $this->customerMetadataService = $customerMetadataService;
        $this->logger = $logger;
    }

    /**
     * After Plugin
     * This After Plugin is used to change the order of First and Last Name
     * @param ViewAlias $subject
     * @param $result
     * @param CustomerInterface $customerData
     * @return string
     */
    public function afterGetCustomerName(ViewAlias $subject, $result, CustomerInterface $customerData)
    {
        $name = '';
        try {
            $prefixMetadata = $this->customerMetadataService->getAttributeMetadata('prefix');
            if ($prefixMetadata->isVisible() && $customerData->getPrefix()) {
                $name .= $customerData->getPrefix() . ' ';
            }

            $name .= $customerData->getLastname();

            $middleNameMetadata = $this->customerMetadataService->getAttributeMetadata('middlename');
            if ($middleNameMetadata->isVisible() && $customerData->getMiddlename()) {
                $name .= ' ' . $customerData->getMiddlename();
            }

            $name .= ' ' . $customerData->getFirstname();

            $suffixMetadata = $this->customerMetadataService->getAttributeMetadata('suffix');
            if ($suffixMetadata->isVisible() && $customerData->getSuffix()) {
                $name .= ' ' . $customerData->getSuffix();
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        $result = $name;
        return $result;
    }
}
