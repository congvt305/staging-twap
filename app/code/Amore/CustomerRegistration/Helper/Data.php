<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: Abbas
 * Date: 05/18/20
 * Time: 9:02 PM
 */

namespace Amore\CustomerRegistration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * This class is used for StoreSms configuration data
 *
 * Class Data
 *
 */
class Data extends AbstractHelper
{
    /**
     * store constants
     */
    const POS_TERMS_CMS_BLOCK_ID = 'customerregistraion/general/terms_cms_block_id';

    /**
     * Get cms block id set in setting
     * Get cms block id set in setting from admin setting
     * @return null|string
     */
    public function getTermsCMSBlockId()
    {
        return $this->scopeConfig->getValue(
                self::POS_TERMS_CMS_BLOCK_ID,
                ScopeInterface::SCOPE_WEBSITE
            );
    }




}
