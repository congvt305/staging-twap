<?php
namespace Sapt\CustomWidget\Block\Widget;

use Sapt\CustomWidget\Block\Widget\AbstractBanner;

class BestSellerProductBanner extends AbstractBanner
{
    // TODO: The bestseller model will be provided by the bo team
    public function getEntityIds() {
        // TODO: MOCK DATA
        //return [75,72,69,66,63.60,57,54,51];
        return [];
    }
}
