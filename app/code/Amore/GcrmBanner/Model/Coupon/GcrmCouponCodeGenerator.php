<?php
declare(strict_types=1);
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 07/26/2021
 */

namespace Amore\GcrmBanner\Model\Coupon;

use Magento\Framework\Math\Random;
use Magento\SalesRule\Helper\Coupon;
use Magento\Framework\DataObject;
use Magento\SalesRule\Model\Coupon\CodegeneratorInterface;

class GcrmCouponCodeGenerator extends DataObject implements CodegeneratorInterface
{

    const SPLIT = 3;
    const LENGTH = 9;

    /**
     * @var Coupon
     */
    private $salesRuleCoupon;

    /**
     * @param Coupon $salesRuleCoupon
     * @param array $data
     */
    public function __construct(
        Coupon $salesRuleCoupon,
        array $data = []
    ) {
        $this->salesRuleCoupon = $salesRuleCoupon;
        parent::__construct($data);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateCode()
    {
        $prefix = $this->getData('codePrefix') ?: 'GCRM';
        $suffix = $this->getData('codeSuffix') ?: '';
        $format = in_array(
            $codeFormat = $this->getData('codeFormat'),
            array_keys($this->salesRuleCoupon->getFormatsList())
        )
            ? $codeFormat
            : Coupon::COUPON_FORMAT_ALPHANUMERIC;

        $charset = $this->salesRuleCoupon->getCharset($format);
        $charsetSize = count($charset);
        $splitChar = $this->getDelimiter();
        $code = '';

        for ($i = 0; $i < self::LENGTH; ++$i) {
            $char = $charset[Random::getRandomNumber(0, $charsetSize - 1)];
            if ($i % self::SPLIT === 0 && $i !== 0) {
                $char = $splitChar . $char;
            }
            $code .= $char;
        }

        return $prefix . $code . $suffix;
    }

    /**
     * Retrieve delimiter
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->salesRuleCoupon->getCodeSeparator() ?? '-';
    }
}
