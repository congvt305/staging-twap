<?php
declare(strict_types=1);

/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 07/25/2021
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Eguana_ScheduledImportExport', __DIR__);
