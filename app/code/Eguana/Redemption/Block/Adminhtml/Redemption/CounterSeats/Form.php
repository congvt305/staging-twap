<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 13/1/21
 * Time: 5:10 PM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Block\Adminhtml\Redemption\CounterSeats;

use Eguana\Redemption\Api\RedemptionRepositoryInterface;
use Eguana\Redemption\Model\Source\AvailableStores;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Psr\Log\LoggerInterface;

/**
 * Redemption counter seats form
 *
 * Class Form
 */
class Form extends Generic
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RedemptionRepositoryInterface
     */
    private $redemptionRepository;

    /**
     * @var AvailableStores
     */
    private $availableStores;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param LoggerInterface $logger
     * @param AvailableStores $availableStores
     * @param RequestInterface $request
     * @param DataPersistorInterface $dataPersistor
     * @param RedemptionRepositoryInterface $redemptionRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        LoggerInterface $logger,
        AvailableStores $availableStores,
        RequestInterface $request,
        DataPersistorInterface $dataPersistor,
        RedemptionRepositoryInterface $redemptionRepository,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->dataPersistor = $dataPersistor;
        $this->availableStores = $availableStores;
        $this->redemptionRepository = $redemptionRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare counter seats input form
     *
     * @return Form
     */
    protected function _prepareForm() : Form
    {
        try {
            $id = $this->request->getParam('redemption_id');
            $selectedStoreId = $this->request->getParam('storeId');
            $counterIds = $this->request->getParam('counterIds');
            $formData = $this->dataPersistor->get('eguana_redemption');
            if ($formData) {
                $offlineStoreIds = $formData['offline_store_id'];
                $counterSeats = $formData['counter_seats'];
                $storeId = isset($formData['store_id']) ? $formData['store_id'] : $formData['store_id_name'];
            } elseif ($id) {
                $redemption = $this->redemptionRepository->getById($id);
                $offlineStoreIds = $redemption->getData('offline_store_id');
                $counterSeats = $redemption->getData('counter_seats');
                $storeId = $redemption->getData('store_id');
            } else {
                $offlineStoreIds = $counterIds;
                $counterSeats = [];
                $storeId = $selectedStoreId;
            }

            if ($offlineStoreIds && $storeId) {
                if (is_array($storeId) && isset($storeId[0])) {
                    $storeId = $storeId[0];
                }

                $form = $this->_formFactory->create();
                $form->setHtmlIdPrefix('redemption_');

                $fieldset = $form->addFieldset('counter_seats_fieldset', []);
                $fieldset->addClass('ignore-validate');

                $storeLocators = $this->availableStores->getCountersByStoreId($storeId, $offlineStoreIds);

                $i = 0;
                foreach ($storeLocators as $store) {
                    $elementId = 'qty_' . $i;
                    $fieldLabel = $store->getTitle() . ' (' . __('Total Quantity') . ')';
                    $counterKey = array_search($store->getEntityId(), $offlineStoreIds);
                    $fieldValue = isset($counterSeats[$counterKey]) ? $counterSeats[$counterKey] : 0;

                    $fieldset->addField(
                        $elementId,
                        'text',
                        [
                            'name' => 'counter_total_seats[' . $i . ']',
                            'label' => $fieldLabel,
                            'title' => $fieldLabel,
                            'required' => true,
                            'class' => 'validate-digits',
                            'value' => $fieldValue,
                            'data-form-part' => 'redemption_redemption_form'
                        ]
                    );

                    $i++;
                }

                $this->setForm($form);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return parent::_prepareForm();
    }
}
