<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/10/20
 * Time: 1:48 PM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Validator;

interface ValidatorInterface
{
    public function validate(array $validateSubject);

}