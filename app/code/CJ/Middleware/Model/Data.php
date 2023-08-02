<?php

namespace CJ\Middleware\Model;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Data
{
    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @param TimezoneInterface $timezoneInterface
     */
    public function __construct(
        TimezoneInterface $timezoneInterface
    ) {
      $this->timezoneInterface = $timezoneInterface;
    }

    /**
     * Format price
     *
     * @param $price
     * @param $isDecimal
     * @return float|string
     */
    public function formatPrice($price, $isDecimal = false)
    {
        if ($isDecimal) {
            return number_format($price, 2, '.', '');
        }
        return $price;
    }

    /**
     * Round price
     *
     * @param $price
     * @param $isDecimal
     * @return float
     */
    public function roundingPrice($price, $isDecimal = false)
    {
        $precision = $isDecimal ? 2 : 0;
        return round($price, $precision);
    }

    /**
     * Format date
     *
     * @param $date
     * @param $format
     * @return string
     */
    public function dateFormatting($date, $format)
    {
        return $this->timezoneInterface->date($date)->format($format);
    }


    /**
     * Correct price again
     *
     * @param $orderAmount
     * @param $itemsAmount
     * @param $orderItemData
     * @param $field
     * @param $isDecimalFormat
     * @return array
     */
    public function priceCorrector($orderAmount, $itemsAmount, $orderItemData, $field, $isDecimalFormat = false)
    {
        if ($orderAmount != $itemsAmount) {
            $correctAmount = $orderAmount - $itemsAmount;

            foreach ($orderItemData as $key => $value) {
                if ($value['itemFgflg'] == 'Y') {
                    continue;
                }
                $orderItemData[$key][$field] = $this->formatPrice($value[$field] + $correctAmount, $isDecimalFormat);
                //when child in bundle item(dynamic price) has discount > subtotal and other order item has special price( catalog price, tier price)
                //so when calculate child ratio for each item the $orderItem->getOriginalPrice(), it will get the price include special price (not normal price)
                // -> when correct data price, may be 'itemFgflg' will be changed
                if ($field == 'itemSlamt') {
                    $orderItemData[$key]['itemFgflg'] = ($orderItemData[$key][$field] == 0 ? 'Y' : 'N');
                }
                break;
            }
        }

        return $orderItemData;
    }

}
