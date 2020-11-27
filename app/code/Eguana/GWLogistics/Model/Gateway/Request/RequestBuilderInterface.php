<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/10/20
 * Time: 9:41 AM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Request;

interface RequestBuilderInterface
{

    /**
     * @param array $buildSubject
     * @return array|null
     */
    public function build(array $buildSubject): ?array;

}