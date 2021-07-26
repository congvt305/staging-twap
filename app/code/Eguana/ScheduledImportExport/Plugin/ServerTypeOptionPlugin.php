<?php
declare(strict_types=1);
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 07/25/2021
 */

namespace Eguana\ScheduledImportExport\Plugin;

use Magento\ScheduledImportExport\Model\Scheduled\Operation\Data;

class ServerTypeOptionPlugin
{
    /**
     * @param \Magento\ScheduledImportExport\Model\Scheduled\Operation\Data $subject
     * @param $result
     */
    public function afterGetServerTypesOptionArray(\Magento\ScheduledImportExport\Model\Scheduled\Operation\Data $subject, $result)
    {
        $resultWithSftp = array_merge($result, ['sftp' => __('Remote SFTP')]);
        return $resultWithSftp;
    }
}
