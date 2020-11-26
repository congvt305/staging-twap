<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/12/20
 * Time: 9:57 AM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Validator;

use Magento\Framework\Phrase;

interface ResultInterface
{
    /**
     * Returns validation result
     *
     * @return bool
     */
    public function isValid();

    /**
     * Returns list of fails description
     *
     * @return Phrase[]
     */
    public function getFailsDescription();

    /**
     * Returns list of error codes.
     *
     * @return string[]
     */
    public function getErrorCodes();

}