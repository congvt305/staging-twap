<?php
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 05. 25. 2021
 */

namespace Eguana\Dhl\Model\ResourceModel\Carrier\Tablerate;

class RateQuery
{
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateRequest
     */
    private $request;

    public function __construct(
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    ) {
        $this->request = $request;
    }

    public function prepareSelect(\Magento\Framework\DB\Select $select)
    {
        $select->where(
            'website_id = :website_id'
        )->order(
            ['dest_country_id DESC', 'dest_region_id DESC', 'dest_city DESC', 'condition_value DESC']
        )->limit(
            1
        );

        // Render destination condition
        $orWhere = '(' . implode(
                ') OR (',
                [
                    "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = :city",
                    "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = ''",

                    // Handle asterisk in dest_zip field
                    "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_city = '*'",
                    "dest_country_id = :country_id AND dest_region_id = 0 AND dest_city = '*'",
                    "dest_country_id = '0' AND dest_region_id = :region_id AND dest_city = '*'",
                    "dest_country_id = '0' AND dest_region_id = 0 AND dest_city = '*'",
                    "dest_country_id = :country_id AND dest_region_id = 0 AND dest_city = ''",
                    "dest_country_id = :country_id AND dest_region_id = 0 AND dest_city = :city",
                ]
            ) . ')';
        $select->where($orWhere);

        // Render condition by condition name
        if (is_array($this->request->getConditionName())) {
            $orWhere = [];
            foreach (range(0, count($this->request->getConditionName())) as $conditionNumber) {
                $bindNameKey = sprintf(':condition_name_%d', $conditionNumber);
                $bindValueKey = sprintf(':condition_value_%d', $conditionNumber);
                $orWhere[] = "(condition_name = {$bindNameKey} AND condition_value <= {$bindValueKey})";
            }

            if ($orWhere) {
                $select->where(implode(' OR ', $orWhere));
            }
        } else {
            $select->where('condition_name = :condition_name');
            $select->where('condition_value <= :condition_value');
        }
        return $select;

    }

    /**
     * Returns query bindings
     *
     * @return array
     */
    public function getBindings()
    {
        $bind = [
            ':website_id' => (int)$this->request->getWebsiteId(),
            ':country_id' => $this->request->getDestCountryId(),
            ':region_id' => (int)$this->request->getDestRegionId(),
            ':city' => $this->request->getDestCity(),
        ];

        // Render condition by condition name
        if (is_array($this->request->getConditionName())) {
            $i = 0;
            foreach ($this->request->getConditionName() as $conditionName) {
                $bindNameKey = sprintf(':condition_name_%d', $i);
                $bindValueKey = sprintf(':condition_value_%d', $i);
                $bind[$bindNameKey] = $conditionName;
                $bind[$bindValueKey] = $this->request->getData($conditionName);
                $i++;
            }
        } else {
            $bind[':condition_name'] = $this->request->getConditionName();
            $bind[':condition_value'] = round($this->request->getData($this->request->getConditionName()), 4);
        }

        return $bind;
    }

    /**
     * Returns rate request
     *
     * @return \Magento\Quote\Model\Quote\Address\RateRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

}
