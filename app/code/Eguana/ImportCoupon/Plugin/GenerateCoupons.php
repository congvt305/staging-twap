<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 7/1/21
 * Time: 10:27 PM
 */
namespace Eguana\ImportCoupon\Plugin;

use Eguana\ImportCoupon\Model\ImportCsv\GenerateCodes;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\SalesRule\Model\ResourceModel\Rule;

/**
 * Plugin to generate after rule is saved
 *
 * Class GenerateCoupons
 */
class GenerateCoupons
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var GenerateCodes
     */
    private $generateCodes;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param GenerateCodes $generateCodes
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        GenerateCodes $generateCodes,
        RequestInterface $request,
        ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->generateCodes = $generateCodes;
        $this->messageManager = $messageManager;
    }

    /**
     * After save function to save coupon codes if file is uploaded
     *
     * @param Rule $subject
     * @param $result
     * @return mixed
     */
    public function afterSave(
        Rule $subject,
        $result
    ) {
        $postData = $this->request->getParams();
        $ruleId = isset($postData['rule_id']) ? $postData['rule_id'] : 0;
        $couponType = isset($postData['coupon_type']) ? $postData['coupon_type'] : 0;
        $autoGeneration = isset($postData['use_auto_generation']) ? $postData['use_auto_generation'] : 0;
        if (isset($postData['coupon_csv_file'][0]['file']) && $ruleId && $autoGeneration && $couponType == 2) {
            $fileName = $postData['coupon_csv_file'][0]['file'];
            $this->generateCodes->importCouponCodes($fileName, $postData['rule_id']);
        }
        return $result;
    }
}
