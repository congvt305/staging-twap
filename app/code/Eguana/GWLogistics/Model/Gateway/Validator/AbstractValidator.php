<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/10/20
 * Time: 1:50 PM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Validator;

abstract class AbstractValidator implements \Eguana\GWLogistics\Model\Gateway\Validator\ValidatorInterface
{
    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    public function __construct(
        \Eguana\GWLogistics\Model\Gateway\Validator\ResultInterfaceFactory $resultFactory
    ) {
        $this->resultFactory = $resultFactory;
    }

    protected function createResult($isValid, array $fails = [], array $errorCodes = [])
    {
        return $this->resultFactory->create(
            [
                'isValid' => (bool)$isValid,
                'failsDescription' => $fails,
                'errorCodes' => $errorCodes
            ]
        );
    }

}