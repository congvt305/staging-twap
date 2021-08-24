<?php

namespace Eguana\EventReservation\Block\Event;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class TextBanner
 * @package Eguana\EventReservation\Block\Event
 */
class TextBanner extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Eguana_EventReservation::text-banner-footer.phtml';

    /**
     * @var \Eguana\EventReservation\Api\EventRepositoryInterface
     */
    protected $eventReservationRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * TextBanner constructor.
     * @param \Eguana\EventReservation\Api\EventRepositoryInterface $eventReservationRepository
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Eguana\EventReservation\Api\EventRepositoryInterface $eventReservationRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->eventReservationRepository = $eventReservationRepository;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    private function getFixedTextBannerEnabled() {
        $config_key = 'event_reservation/configuration/fixed_text_banner_enabled';

        try {
            $storeId = $this->storeManager->getStore()->getId();
            return $this->_scopeConfig->getValue($config_key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } catch (NoSuchEntityException $e) {
        }

        return '';
    }

    /**
     * @return string
     */
    private function getReservationId() {
        return $this->_request->getParam('id', '');
    }

    /**
     * @return string
     */
    public function getMessage() {
        if ($this->getFixedTextBannerEnabled()) {

            if($id = $this->getReservationId()) {
                try {
                    $reservation = $this->eventReservationRepository->getById($id);
                    return $reservation->getFixedBannerMessage();
                } catch (\Exception $exception) {
                    return '';
                }
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getBgColor() {
        if ($this->getFixedTextBannerEnabled()) {
            if($id = $this->getReservationId()) {
                try {
                    $reservation = $this->eventReservationRepository->getById($id);
                    return $reservation->getFixedBannerBgColor();
                } catch (\Exception $exception) {
                    return '';
                }
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getHyperlink() {
        if($id = $this->getReservationId()) {
            try {
                $reservation = $this->eventReservationRepository->getById($id);
                return $reservation->getFixedBannerHyperlink();
            } catch (\Exception $exception) {
                return '';
            }
        }

        return '';
    }
}