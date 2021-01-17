<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 10/1/21
 * Time: 04:01 PM
 */
declare(strict_types=1);

namespace Eguana\ImportCoupon\Ui\Component\Form\Fieldset;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Form\Fieldset;

/**
 * To disable upload csv file fieldset
 *
 * Class UploadCsvFile
 */
class UploadCsvFile extends Fieldset
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     * @param ContextInterface $context
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        RequestInterface $request,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->request = $request;
    }

    /**
     * Prepare component configuration
     * To disable upload csv file fieldset if create rule page is called
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        if (!$this->request->getParam('id')) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }
}
