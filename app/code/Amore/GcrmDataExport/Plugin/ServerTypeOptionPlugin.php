<?php
declare(strict_types=1);
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 07/25/2021
 */

namespace Amore\GcrmDataExport\Plugin;

use Magento\ScheduledImportExport\Model\Scheduled\Operation\Data;

class ServerTypeOptionPlugin
{
    /**
     * @param Data $subject
     * @param $result
     */
    public function afterGetServerTypesOptionArray(Data $subject, $result)
    {
        $resultWithSftp = array_merge($result, ['sftp' => __('Remote SFTP')]);
        return $resultWithSftp;
    }
}
