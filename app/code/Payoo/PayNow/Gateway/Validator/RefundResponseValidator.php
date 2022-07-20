<?php

namespace Payoo\PayNow\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;

class RefundResponseValidator extends AbstractValidator
{
    /**
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }
        $response = $validationSubject['response'];
        if (isset($response['ResponseData'])) {
            $code = json_decode($response['ResponseData'], true);
            if (isset($code['ResponseCode'])) {
                if ($code['ResponseCode'] == 0) {
                    return $this->createResult(
                        true,
                        []
                    );
                } else {
                    return $this->createResult(
                        false,
                        [__($this->parseErrorReponse($code['ResponseCode']))]
                    );
                }
            }
        }
        throw new \InvalidArgumentException('Response format is wrong');
    }

    /**
     * @param $code
     * @return string
     */
    private function parseErrorReponse($code)
    {
        switch ($code) {
            case 3:
                $message = 'Business (Shop) does not exist.';
                break;
            case 8:
                $message = 'The order does not exist.';
                break;
            case 9:
                $message = 'Invalid amount.';
                break;
            case 83:
                $message = 'The amount is less than the minimum.';
                break;
            case 91:
                $message = 'The account receiving the refund (buyer) is locked.';
                break;
            case 92:
                $message = 'The total amount refunded is greater than the limit for the day.';
                break;
            case 94:
                $message = 'Order "temporarily suspended" (due to admin intervention, need to contact Payoo for more details on order status.).';
                break;
            case 96:
                $message = 'Business (Shop) does not have permission to call API';
                break;
            case 97:
                $message = 'The refund amount is greater than the total order amount.';
                break;
            case 98:
                $message = 'Order was canceled.';
                break;
            case 99:
                $message = 'The partner\'s refund code (refundId) already exists.';
                break;
            default:
                $message = 'System error.';
                break;
        }
        return $message;
    }
}
