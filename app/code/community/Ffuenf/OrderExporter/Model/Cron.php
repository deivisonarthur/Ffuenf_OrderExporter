<?php
/**
 * Ffuenf_OrderExporter extension.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category   Ffuenf
 *
 * @author     Achim Rosenhagen <a.rosenhagen@ffuenf.de>
 * @copyright  Copyright (c) 2018 ffuenf (http://www.ffuenf.de)
 * @license    http://opensource.org/licenses/mit-license.php MIT License
 */

class Ffuenf_OrderExporter_Model_Cron extends Mage_Core_Model_Abstract
{
    /**
     * Export orders.
     * This method will be called via a Magento crontab task.
     *
     * @return null|Ffuenf_OrderExporter_Model_Cron
     */
    public function export()
    {
        if (!Mage::helper('ffuenf_orderexporter')->isExtensionActive()) {
            return;
        }
        # [TODO] Basically run the logic from Ffuenf_OrderExporter_Adminhtml_ExporterController::exportallAction but do not return the export file, but write it somewhere to disc (e.g. var/export)
        
        return $this;
    }
}
