<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 8/1/21
 * Time: 12:25 AM
 */
declare(strict_types=1);

namespace Eguana\ImportCoupon\Model\ImportCsv;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

/**
 * To import codes from csv and generate coupon codes
 *
 * Class GenerateCodes
 */
class GenerateCodes
{
    /**
     * @var string
     */
    const FILE_DIR = 'importCoupon/tmp';

    /**
     * @var Csv
     */
    private $csv;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var CouponFactory
     */
    private $couponFactory;

    /**
     * @var CouponRepositoryInterface
     */
    private $couponRepository;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var TimezoneInterface
     */
    private $timeZone;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Csv $csv
     * @param Filesystem $filesystem
     * @param CouponFactory $couponFactory
     * @param LoggerInterface $logger
     * @param TimezoneInterface $timeZone
     * @param RuleRepositoryInterface $ruleRepository
     * @param CouponRepositoryInterface $couponRepository
     */
    public function __construct(
        Csv $csv,
        Filesystem $filesystem,
        CouponFactory $couponFactory,
        LoggerInterface $logger,
        TimezoneInterface $timeZone,
        RuleRepositoryInterface $ruleRepository,
        CouponRepositoryInterface $couponRepository
    ) {
        $this->csv = $csv;
        $this->logger = $logger;
        $this->timeZone = $timeZone;
        $this->filesystem = $filesystem;
        $this->couponFactory = $couponFactory;
        $this->ruleRepository = $ruleRepository;
        $this->couponRepository = $couponRepository;
    }

    /**
     * To import coupons from csv file and generate codes
     *
     * @param $fileName
     * @param $ruleId
     */
    public function importCouponCodes($fileName, $ruleId)
    {
        try {
            if ($fileName && $ruleId) {
                $mediapath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
                $csvData = $this->csv->getData($mediapath . self::FILE_DIR . '/' . $fileName);

                /** @var Rule $rule */
                $rule = $this->ruleRepository->getById($ruleId);
                if ($rule->getRuleId()) {
                    foreach ($csvData as $row => $data) {
                        if ($row > 0) {
                            $code = isset($data[0]) ? $data[0] : '';
                            $this->createCoupon($rule, $code);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error while generating codes:' . $e->getMessage());
        }
    }

    /**
     * To create coupon code
     *
     * @param Rule $rule
     * @param $code
     */
    private function createCoupon($rule, $code)
    {
        if ($rule && $rule->getRuleId() && $code) {
            try {
                $createdAt = $this->timeZone->date()->format('Y-m-d H:i:s');
                $coupon = $this->couponFactory->create();
                $coupon->setCode($code)
                    ->setType(1)
                    ->setCreatedAt($createdAt)
                    ->setRuleId($rule->getRuleId())
                    ->setExpirationDate($rule->getToDate())
                    ->setUsageLimit($rule->getUsesPerCoupon())
                    ->setUsagePerCustomer($rule->getUsesPerCustomer());

                $this->couponRepository->save($coupon);
            } catch (\Exception $e) {
                $this->logger->error('Error while creating Coupon code:' . $e->getMessage());
            }
        }
    }
}
