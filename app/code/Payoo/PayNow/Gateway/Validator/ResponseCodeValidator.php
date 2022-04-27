<?php

namespace Payoo\PayNow\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;


class ResponseCodeValidator extends AbstractValidator
{
    const RESULT_CODE = 'RESULT_CODE';

    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];

        if ($response['result'] !== 'success') {
            return $this->createResult(
                false,
                [__($response['message'])]
            );
        }

        return $this->createResult(
            true,
            []
        );
    }

}
