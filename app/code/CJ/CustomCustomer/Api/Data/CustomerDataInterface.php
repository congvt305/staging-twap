<?php

namespace CJ\CustomCustomer\Api\Data;

/**
 * Interface CustomerDataInterface
 */
interface CustomerDataInterface
{
    const CSTM_INTG_SEQ = 'cstmIntgSeq';

    const CSTM_GRADE_N_M = 'cstmGradeNM';

    const CSTM_GRADE_C_D = 'cstmGradeCD';

    /**
     * @return string
     */
    public function getCstmIntgSeq():string;

    /**
     * @param string|null $intgSeq
     * @return $this
     */
    public function setCstmIntgSeq(?string $intgSeq);

    /**
     * @return string
     */
    public function getCstmGradeNM():string;

    /**
     * @param string|null $gradeNM
     * @return $this
     */
    public function setCstmGradeNM(?string $gradeNM);

    /**
     * @return string
     */
    public function getCstmGradeCD():string;

    /**
     * @param string|null $gradePrefix
     * @return $this
     */
    public function setCstmGradeCD(?string $gradePrefix);

}
