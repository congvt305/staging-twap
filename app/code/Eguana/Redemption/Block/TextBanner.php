<?php

namespace Eguana\Redemption\Block;

/**
 * Class TextBanner
 * @package Eguana\Redemption\Block
 */
class TextBanner extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'Eguana_Redemption::text-banner-footer.phtml';

    /**
     * @var \Eguana\Redemption\Api\RedemptionRepositoryInterface
     */
    private $redemptionRepository;

    /**
     * @var \Eguana\Redemption\Model\RedemptionConfiguration\RedemptionConfiguration
     */
    private $redemptionConfiguration;

    const LAYOUT_HANDLER_INDEX = 'redemption_details_index';
    const LAYOUT_HANDLER_SUCCESS = 'redemption_details_success';

    /**
     * TextBanner constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Eguana\Redemption\Api\RedemptionRepositoryInterface $redemptionRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Eguana\Redemption\Api\RedemptionRepositoryInterface $redemptionRepository,
        \Eguana\Redemption\Model\RedemptionConfiguration\RedemptionConfiguration $redemptionConfiguration,
        array $data = []
    ) {
        $this->redemptionConfiguration = $redemptionConfiguration;
        $this->redemptionRepository = $redemptionRepository;
        parent::__construct($context, $data);
    }


    /**
     * @return string
     */
    public function getMessage() {

        if ($this->redemptionConfiguration->getFixedTextBannerEnabled()) {
            $redemptionId = $this->_request->getParam('redemption_id');

            if ($redemptionId) {
                $redemption = $this->redemptionRepository->getById($redemptionId);
                if ($redemption->getId()) {
                    $layoutHandler = $this->getData('layout_handler');
                    if ($layoutHandler == self::LAYOUT_HANDLER_INDEX) {
                        return $redemption->getTextBannerIndex();
                    } elseif ($layoutHandler == self::LAYOUT_HANDLER_SUCCESS) {
                        return $redemption->getTextBannerSuccess();
                    }
                }
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getBgColor() {
        if ($this->redemptionConfiguration->getFixedTextBannerEnabled()) {
            $redemptionId = $this->_request->getParam('redemption_id');

            if ($redemptionId) {
                $redemption = $this->redemptionRepository->getById($redemptionId);
                if ($redemption->getId()) {
                    return $redemption->getBgColorTextBanner();
                }
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getHyperlink() {
        if ($this->redemptionConfiguration->getFixedTextBannerEnabled()) {
            $redemptionId = $this->_request->getParam('redemption_id');

            if ($redemptionId) {
                $redemption = $this->redemptionRepository->getById($redemptionId);
                if ($redemption->getId()) {
                    $layoutHandler = $this->getData('layout_handler');
                    if ($layoutHandler == self::LAYOUT_HANDLER_INDEX) {
                        return $redemption->getTextBannerIndexHyperlink();
                    } elseif ($layoutHandler == self::LAYOUT_HANDLER_SUCCESS) {
                        return $redemption->getTextBannerSuccessHyperlink();
                    }
                }
            }
        }

        return '';
    }
}