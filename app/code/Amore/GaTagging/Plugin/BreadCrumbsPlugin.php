<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 9/4/20
 * Time: 8:06 AM
 */

namespace Amore\GaTagging\Plugin;


use Magento\Theme\Block\Html\Breadcrumbs;

class BreadCrumbsPlugin
{

    /**
     * @param \Magento\Theme\Block\Html\Breadcrumbs $subject
     * @param $result
     */
    public function afterToHtml(\Magento\Theme\Block\Html\Breadcrumbs $subject, $result)
    {
        $breadCrumbText = "";
        if ($result) {
            $breadCrumbText = str_replace('</li>', '>', $result);
            $breadCrumbText = strip_tags($breadCrumbText);
            $breadCrumbText = str_replace(["\n", ' '], '', $breadCrumbText);
            $breadCrumbText = substr($breadCrumbText, 0, -1);
        }
        $apScript = '<script>var AP_DATA_BREAD = ' . '"' . $breadCrumbText . '"'.'; </script>';
        return $result . $apScript;
    }
}