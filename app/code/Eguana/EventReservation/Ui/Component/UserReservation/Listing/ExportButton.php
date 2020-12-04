<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 16/11/20
 * Time: 10:38 PM
 */
namespace Eguana\EventReservation\Ui\Component\UserReservation\Listing;

use Magento\Ui\Component\ExportButton as UiExportButton;

/**
 * Class for showing only csv & xls buttons
 *
 * Class ExportButton
 */
class ExportButton extends UiExportButton
{
    /**
     * Prepare function
     *
     * @return void
     */
    public function prepare()
    {
        $context = $this->getContext();
        $config = $this->getData('config');
        if (isset($config['options'])) {
            $options = [];
            foreach ($config['options'] as $option) {
                if ($option['value'] != 'xml') {
                    $additionalParams = $this->getAdditionalParams($config, $context);
                    $option['url'] = $this->urlBuilder->getUrl($option['url'], $additionalParams);
                    $options[] = $option;
                }
            }
            $config['options'] = $options;
            $this->setData('config', $config);
        }
        parent::prepare();
    }
}
