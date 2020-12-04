<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 6/10/20
 * Time: 3:27 PM
 */
namespace Eguana\LinePay\Gateway\Validator;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Eguana\LinePay\Gateway\Validator\ErrorCodeProvider;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Eguana\LinePay\Gateway\SubjectReader;

/**
 * Class ResponseValidator
 *
 * Class ResponseValidator validate
 */
class ResponseValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{

    /**
     * @var ValidatorInterface[] | TMap
     */
    private $validators;

    /**
     * @var array
     */
    private $chainBreakingValidators;

    /**
     * @var ErrorCodeProvider
     */
    private $errorCodeProvider;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * ResponseValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param TMapFactory $tmapFactory
     * @param SubjectReader $subjectReader
     * @param ErrorCodeProvider $errorCodeProvider
     * @param array $validators
     * @param array $chainBreakingValidators
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        TMapFactory $tmapFactory,
        SubjectReader $subjectReader,
        ErrorCodeProvider $errorCodeProvider,
        array $validators = [],
        array $chainBreakingValidators = []
    ) {
        $this->subjectReader = $subjectReader;
        $this->errorCodeProvider = $errorCodeProvider;
        $this->validators = $tmapFactory->create(
            [
                'array' => $validators,
                'type' => ValidatorInterface::class
            ]
        );
        $this->chainBreakingValidators = $chainBreakingValidators;
        parent::__construct($resultFactory);
    }

    /**
     * Validate response
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponseObject($validationSubject);
        $isValid = true;
        $errorMessages = [];
        foreach ($this->getResponseValidators() as $validator) {
            $validationResult = $validator($response);
            if (!$validationResult[0]) {
                $isValid = $validationResult[0];
                $errorMessages = array_merge($errorMessages, $validationResult[1]);
            }
        }
        $errorCodes = $this->errorCodeProvider->getErrorCodes($response);
        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }

    /**
     * @return array
     */
    protected function getResponseValidators()
    {
        return [
            function ($response) {
                return [
                    isset($response['returnCode']) && $response['returnCode'] == "0000",
                    [$response['returnMessage'] ?? __('Linepay error response.')]
                ];
            }
        ];
    }
}
