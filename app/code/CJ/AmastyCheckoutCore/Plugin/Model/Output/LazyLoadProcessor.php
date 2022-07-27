<?php
declare(strict_types=1);

namespace CJ\AmastyCheckoutCore\Plugin\Model\Output;

use Amasty\LazyLoad\Model\Asset\Collector\PreloadImageCollector;
use Amasty\LazyLoad\Model\ConfigProvider;
use Amasty\LazyLoad\Model\LazyScript\LazyScriptProvider;
use Amasty\LazyLoad\Model\OptionSource\PreloadStrategy;
use Amasty\LazyLoad\Model\Output\LazyConfig\LazyConfig;
use Amasty\LazyLoad\Model\Output\LazyConfig\LazyConfigFactory;
use Amasty\PageSpeedTools\Model\Image\ReplacerCompositeInterface;
use Amasty\PageSpeedTools\Model\Output\OutputProcessorInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\StoreManagerInterface;

class LazyLoadProcessor extends \Amasty\LazyLoad\Model\Output\LazyLoadProcessor
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * LazyLoadProcessor constructor.
     * @param ConfigProvider $configProvider
     * @param Repository $assetRepo
     * @param LazyScriptProvider $lazyScriptProvider
     * @param LazyConfigFactory $lazyConfigFactory
     * @param PreloadImageCollector $preloadImageCollector
     * @param ReplacerCompositeInterface $imageReplacer
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ConfigProvider $configProvider,
        Repository $assetRepo,
        LazyScriptProvider $lazyScriptProvider,
        LazyConfigFactory $lazyConfigFactory,
        PreloadImageCollector $preloadImageCollector,
        ReplacerCompositeInterface $imageReplacer,
        StoreManagerInterface $storeManager
    )
    {
        parent::__construct($configProvider, $assetRepo, $lazyScriptProvider, $lazyConfigFactory, $preloadImageCollector, $imageReplacer);
        $this->storeManager = $storeManager;
    }

    public function aroundProcessLazyImages(\Amasty\LazyLoad\Model\Output\LazyLoadProcessor $sub,callable $proceed,&$output)
    {
        if ($this->storeManager->getStore()->getCode() != self::MY_SWS_STORE_CODE) {
            return $proceed($output);
        }
        $tempOutput = preg_replace('/<script[^>]*>(?>.*?<\/script>)/is', '', $output);
        if($tempOutput){
            return $proceed($output);
        }else{
            return $this;
        }
    }
}
