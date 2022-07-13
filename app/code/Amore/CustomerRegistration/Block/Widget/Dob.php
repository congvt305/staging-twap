<?php
/**
 *  @author Eguana Team
 *  @copyriht Copyright (c) ${YEAR} Eguana {http://eguanacommerce.com}
 *  Created byPhpStorm
 *  User:  Abbas
 *  Date: 7/14/20
 *  Time: 10:30 am
 */

namespace Amore\CustomerRegistration\Block\Widget;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Locale\Bundle\DataBundle;

/**
 * Dob preference to change the dob validation
 *
 * Class Dob
 */
class Dob extends \Magento\Customer\Block\Widget\Dob
{
    /**
     * @var ResolverInterface
     */
    private $localeResolver;
    /**
     * JSON Encoder
     *
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * Dob constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param \Magento\Framework\View\Element\Html\Date $dateElement
     * @param \Magento\Framework\Data\Form\FilterFactory $filterFactory
     * @param array $data
     * @param EncoderInterface|null $encoder
     * @param ResolverInterface|null $localeResolver
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        \Magento\Framework\View\Element\Html\Date $dateElement,
        \Magento\Framework\Data\Form\FilterFactory $filterFactory,
        array $data = [],
        ?EncoderInterface $encoder = null,
        ?ResolverInterface $localeResolver = null
    ) {
        parent::__construct(
            $context,
            $addressHelper,
            $customerMetadata,
            $dateElement,
            $filterFactory,
            $data,
            $encoder,
            $localeResolver
        );
        $this->localeResolver = $localeResolver ?? ObjectManager::getInstance()->get(ResolverInterface::class);
        $this->encoder = $encoder ?? ObjectManager::getInstance()->get(EncoderInterface::class);
    }

    /**
     * While register adding date giving JavaScript warning invalid date.
     * this warning is coming from the file lib/web/mage/validation.js at line 1035. There is momentJs library which
     * validate the format of date. In our case vendor/magento/framework/Stdlib/DateTime/Timezone.php from line 120
     * using PHP class IntlDateFormatter based on locale which is zh_Hant_TW it return y/M/d according to which date
     * should be like 20/Jun/18. But momentJS supposing it should be Y/mm/dd or something else which is generated
     * by Datepicker and acceptable by momentJS.
     *
     * Return data-validate rules
     *
     * @return string
     */
    public function getHtmlExtraParams()
    {
        $validators = [];
        if ($this->isRequired()) {
            $validators['required'] = true;
        }
        $validators['validate-dob-custom'] = [
            'dateFormat' => $this->getDateFormat()
        ];

        return 'data-validate="' . $this->_escaper->escapeHtml(json_encode($validators)) . '"';
    }


    /**
     * Get translated calendar config json formatted
     *
     * @return string
     */
    public function getTranslatedCalendarConfigJson(): string
    {
        $locale = $this->localeResolver->getLocale();
        $storeCode = $this->_storeManager->getStore()->getCode();
        if ($storeCode == 'my_laneige') {
            $locale = 'en_US';
        }
        $localeData = (new DataBundle())->get($locale);
        $monthsData = $localeData['calendar']['gregorian']['monthNames'];
        $daysData = $localeData['calendar']['gregorian']['dayNames'];

        return $this->encoder->encode(
            [
                'closeText' => __('Done'),
                'prevText' => __('Prev'),
                'nextText' => __('Next'),
                'currentText' => __('Today'),
                'monthNames' => array_values(iterator_to_array($monthsData['format']['wide'])),
                'monthNamesShort' => array_values(iterator_to_array($monthsData['format']['abbreviated'])),
                'dayNames' => array_values(iterator_to_array($daysData['format']['wide'])),
                'dayNamesShort' => array_values(iterator_to_array($daysData['format']['abbreviated'])),
                'dayNamesMin' => array_values(iterator_to_array($daysData['format']['short'])),
            ]
        );
    }
}
