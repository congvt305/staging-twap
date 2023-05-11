<?php
declare(strict_types=1);

namespace Amore\StaffReferral\Model;

use Amore\StaffReferral\Api\ReferralApplyResultInterface;

class ReferralApplyResult implements ReferralApplyResultInterface
{
    /**
     * @var string
     */
    private $message;


    /**
     * @return string
     */
    public function getMessage()
    {
        return (string)$this->message;
    }

    /**
     * @param string $message
     * @return ReferralApplyResultInterface
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
}
