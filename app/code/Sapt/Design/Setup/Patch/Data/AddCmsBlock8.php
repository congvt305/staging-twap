<?php
declare(strict_types=1);

namespace Sapt\Design\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;

class AddCmsBlock8 implements DataPatchInterface
{
    /**
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        BlockFactory $blockFactory
    ) {
        $this->blockFactory = $blockFactory;
    }
    /**
     * @inheritdoc
     */
    public function apply()
    {
        $cmsBlockData = [
            'title' => 'Order Failed Content (TW Laneige)',
            'identifier' => 'tw_laneige_order_failed_content',
            'content' => '<style>#html-body [data-pb-style=O8UOPD7]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="O8UOPD7"><div data-content-type="html" data-appearance="default" data-element="main"><div class="service_center">
            <h2 class="title">Service Center</h2>
            <p><strong>Call</strong> <a href="tel:+852 2895 6008">+852 2895 6008</a> (Ofiice hour : 10:00 ~ 19:00)</p>
            <p><strong>Email</strong> <a href="mailto:laneige@amorepacific.com.hk">laneige@amorepacific.com.hk</a></p>
            <p><strong>Go to</strong> <a class="btn_inquiry" href="#">1:1 inquiry</a></p>
        </div></div></div></div><div data-content-type="html" data-appearance="default" data-element="main"><div class="block_title">
        <h2>Customer Favorites</h2>
        <p>Perfect combination with</p>
        </div></div>',
            'is_active' => 1,
            'stores' => [4],
            'sort_order' => 0
        ];
        $this->blockFactory->create()->setData($cmsBlockData)->save();
    }
    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}