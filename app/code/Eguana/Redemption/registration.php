<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/10/20
 * Time: 10:37 AM
 */
use Magento\Framework\Component\ComponentRegistrar;

/**
 * A new module for redemption users
 */
ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Eguana_Redemption',
    __DIR__
);
