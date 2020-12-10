<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 4/11/20
 * Time: 12:49 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Ui\Component\Counter\Listing\Column;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class CounterActions
 *
 * Provide counter actions
 */
class CounterActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param RequestInterface $request
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        RequestInterface $request,
        RedirectInterface $redirect,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->request  = $request;
        $this->redirect = $redirect;
    }

    /**
     * Add counter form link to edit action
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) : array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $counterId = $item['entity_id'];
                $item[$this->getData('name')] = '<a id="openModal" class="counter-grid-href" data-event-id="' .
                    $this->request->getParam('event_id') . '" data-counter-id="' . $counterId . '">Edit</a>';
            }
        }
        return $dataSource;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        $url = $this->redirect->getRefererUrl();
        if (strpos($url, 'event_id') === false) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }
}
