<?php

namespace CJ\CustomCustomer\Api\Data;

interface CustomerDataInterface
{
    const INTEGRATION_NUMBER = 'integration_number';

    const CURRENT_GRADE = 'current_grade';

    public function getIntegrationNumber():string;

    public function setIntegrationNumber(?string $integrationNumber);

    public function getCurrentGrade():string;

    public function setCurrentGrade(?string $currentGrade);
}
