<?php

namespace CJ\CustomCustomer\Api\Data;

/**
 * Interface CustomerDataInterface
 */
interface CustomerDataInterface
{
    const INTEGRATION_SEQUENCE = 'cstmIntgSeq';

    const GRADE_NM = 'cstmGradeNM';

    const GRADE_CD = 'cstmGradeCD';

    const REQUEST_SCOPE = 'scope';

    /**
     * @return string
     */
    public function getIntegrationSequence():string;

    /**
     * @param string|null $intgSeq
     * @return $this
     */
    public function setIntegrationSequence(?string $intgSeq);

    /**
     * @return string
     */
    public function getGradeNm():string;

    /**
     * @param string|null $gradeNM
     * @return $this
     */
    public function setGradeNm(?string $gradeNM);

    /**
     * @return string
     */
    public function getGradeCd():string;

    /**
     * @param string|null $gradePrefix
     * @return $this
     */
    public function setGradeCd(?string $gradePrefix);

    /**
     * Get scope of request
     * possible values: default|tw_laneige|vn_laneige|vn_sulwhasoo|my_laneige|my_sulwhasoo|....
     * it is store code
     *
     * @return string
     */
    public function getScope(): string;

    /**
     * @param string|null $scope
     * @return $this
     */
    public function setScope(string $scope = 'default');
}
