<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 9/6/20
 * Time: 8:05 PM
 */
namespace Eguana\VideoBoard\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Main class to load data from db
 *
 * Class VideoBoard
 */
class VideoBoard extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eguana_video_board', 'entity_id');
    }
}
