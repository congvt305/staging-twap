<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 8/7/20
 * Time: 8:36 AM
 */

namespace Eguana\Directory\Plugin;


use Magento\Directory\Model\ResourceModel\Region\Collection;

class RegionCollectionPlugin
{

    /**
     * @param \Magento\Directory\Model\ResourceModel\Region\Collection $subject
     * @param $result
     */
    public function afterToOptionArray(\Magento\Directory\Model\ResourceModel\Region\Collection $subject, $result)
    {
        usort($result, [$this, 'sortByRegionId']);
        return $result;
    }

    private function sortByRegionId($currentElement, $nextElement)
    {
        if ($currentElement['value'] == $nextElement['value']) {
            return 0;
        }

        return ($currentElement['value'] < $nextElement['value']) ? -1 : 1;
    }
}
