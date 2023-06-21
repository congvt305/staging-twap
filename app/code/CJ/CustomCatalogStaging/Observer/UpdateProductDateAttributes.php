<?php
declare(strict_types=1);

namespace CJ\CustomCatalogStaging\Observer;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Staging\Model\VersionManager;

class UpdateProductDateAttributes extends \Magento\CatalogStaging\Observer\UpdateProductDateAttributes
{
    /**
     * List of start date attributes related to product entity
     *
     * @var array
     */
    private static $startDateKeys = [
        'news_from_date',
        'special_from_date',
        'custom_design_from',
    ];

    /**
     * List of end date attributes related to product entity
     *
     * @var array
     */
    private static $endDateKeys = [
        'news_to_date',
        'special_to_date',
        'custom_design_to',
    ];

    /**
     * List of date attributes
     *
     * @var array
     */
    private static $dateKeys = [
        'news_from_date' => 'is_new',
        'news_to_date' => 'is_new'
    ];


    /**
     * @var VersionManager
     */
    private $versionManager;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;
    /**
     * @var DateTimeFactory|mixed
     */
    private mixed $dateTimeFactory;

    public function __construct(
        VersionManager $versionManager,
        TimezoneInterface $localeDate,
        DateTimeFactory $dateTimeFactory = null
    ) {
        $this->versionManager = $versionManager;
        $this->localeDate = $localeDate;
        $this->dateTimeFactory = $dateTimeFactory ?: ObjectManager::getInstance()->get(DateTimeFactory::class);
        parent::__construct(
            $versionManager,
            $localeDate,
            $dateTimeFactory
        );
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var ProductInterface $product */
        $product = $observer->getEvent()->getProduct();
        $createdIn = (int)$product->getData('created_in');
        $updatedIn = (int)$product->getData('updated_in');

        if ($createdIn && $updatedIn) {
            if ($createdIn > VersionManager::MIN_VERSION) {
                $dateTime = $this->dateTimeFactory->create()->setTimestamp($createdIn);
                $localStartTime = $this->localeDate->date($dateTime);
                $this->setDateTime(
                    $product,
                    self::$startDateKeys,
                    $localStartTime->format(DateTime::DATETIME_PHP_FORMAT)
                );
            } else {

                if ($product->getData('is_new')) {
                    //Custom here get follow locale date
                    $date = $product->getData('news_from_date') ?
                        $this->localeDate->date($product->getData('news_from_date'))->format(DateTime::DATETIME_PHP_FORMAT) :
                        $this->localeDate->date()->format(DateTime::DATETIME_PHP_FORMAT);
                    $this->setDateTime($product, self::$startDateKeys, $date);
                    //End custom
                } else {
                    $this->setDateTime($product, self::$startDateKeys, null);
                }
            }

            if ($updatedIn < VersionManager::MAX_VERSION) {
                $dateTime = $this->dateTimeFactory->create()->setTimestamp($updatedIn);
                $localEndTime = $this->localeDate->date($dateTime);
                $this->setDateTime($product, self::$endDateKeys, $localEndTime->format(DateTime::DATETIME_PHP_FORMAT));
            } else {
                //Custom here to set date follow locale
                if ($product->getData('is_new')) {
                    $date = $product->getData('news_to_date') ?
                    $this->localeDate->date($product->getData('news_to_date'))->format(DateTime::DATETIME_PHP_FORMAT) :
                        $this->localeDate->date()->format(DateTime::DATETIME_PHP_FORMAT);
                    $this->setDateTime($product, self::$endDateKeys, $date);
                } else {
                    $this->setDateTime($product, self::$endDateKeys, null);
                }
            }
        }
    }

    /**
     * Update product datetime attributes with new datetime value
     * The method gets object with \Magento\Catalog\Api\Data\ProductInterface interface, “keys” array with product datetime attributes names (this attributes will be updated) and datetime value to update attributes.
     * In case when is_new attribute value equal to '1' or when is_new attribute value is NULL and product datetime attribute value is stored in database, product datetime attributes will be updated with given datetime value.
     * In other cases product datetime attributes will be updated with NULL.
     *
     * @param ProductInterface $product
     * @param array $keys
     * @param $time
     * @return void
     */
    private function setDateTime(ProductInterface $product, array $keys, $time)
    {
        foreach ($keys as $key) {
            if (!isset(self::$dateKeys[$key])) {
                continue;
            }

            if ($product->getData(self::$dateKeys[$key]) === null) {
                if (!$product->getData($key)) {
                    $time = null;
                }
            } elseif ($product->getData(self::$dateKeys[$key]) === '0') {
                $time = null;
            }

            $product->setData($key, $time);
        }
    }
}
