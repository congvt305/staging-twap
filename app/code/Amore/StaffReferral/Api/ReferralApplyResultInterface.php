<?php
declare(strict_types=1);

namespace Amore\StaffReferral\Api;

interface ReferralApplyResultInterface
{
    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string $message
     * @return ReferralApplyResultInterface
     */
    public function setMessage($message);
}
