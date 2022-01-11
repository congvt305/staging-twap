<?php
namespace CJ\LineShopping\Observer;

use CJ\LineShopping\Helper\Config;
use CJ\LineShopping\Helper\Data as DataHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;

class SaveLineShoppingDataToOrder implements ObserverInterface
{
    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var DataHelper
     */
    protected DataHelper $dataHelper;

    /**
     * @var Json
     */
    protected Json $json;

    /**
     * @param Json $json
     * @param DataHelper $dataHelper
     * @param Config $config
     */
    public function __construct(
        Json $json,
        DataHelper $dataHelper,
        Config $config
    ) {
        $this->json = $json;
        $this->dataHelper = $dataHelper;
        $this->config = $config;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(
        Observer $observer
    ) {
        $order = $observer->getEvent()->getOrder();
        $websiteId = $order->getStore()->getWebsiteId();
        $enable = $this->config->isEnable($websiteId);

        if (!$enable) {
            return $this;
        }
        $lineEcid = $this->dataHelper->getLineEcidCookie();
        $lineInfo = $this->dataHelper->getLineInfomationCookie();
        if (!$lineEcid) {
            return $this;
        }
        if($lineInfo) {
            $data = $this->json->unserialize($lineInfo);
            foreach (DataHelper::LINE_INFO as $item) {
                if (isset($data[$item])) {
                    $order->setData('line_' . $item, $data[$item]);
                }
            }
        }
        $order->setData('line_ecid', $lineEcid);
        $order->setData('is_line_shopping', 1);
        return $this;
    }
}
