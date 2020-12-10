<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 11/11/20
 * Time: 12:51 PM
 */
namespace Eguana\Redemption\Plugin\ReCaptcha\Model;

use MSP\ReCaptcha\Model\LayoutSettings as Subject;
use Eguana\Redemption\Model\RedemptionConfiguration\RedemptionConfiguration;

/**
 * Class LayoutSettings
 * The class responsible for adding redemption zone to MSP_ReCaptcha Layout setting
 */
class LayoutSettings
{
    /**
     * @var RedemptionConfiguration
     */
    private $config;

    /**
     * LayoutSettings constructor.
     * @param RedemptionConfiguration $config
     */
    public function __construct(RedemptionConfiguration $config)
    {
        $this->config = $config;
    }

    /**
     * @param Subject $subject
     * @param array $result
     * @return array
     */
    public function afterGetCaptchaSettings(Subject $subject, array $result)
    {
        if (isset($result['enabled'])) {
            $result['enabled']['redemption'] = $this->config->isEnabledFrontendRecaptcha();
        }
        return $result;
    }
}
