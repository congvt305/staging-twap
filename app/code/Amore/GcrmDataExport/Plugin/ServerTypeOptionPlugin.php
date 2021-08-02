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

/**
 * Class to add new server type in configurations
 *
 * Class ServerTypeOptionPlugin
 */
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
