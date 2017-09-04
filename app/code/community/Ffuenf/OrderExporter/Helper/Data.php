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
 * @copyright  Copyright (c) 2017 ffuenf (http://www.ffuenf.de)
 * @license    http://opensource.org/licenses/mit-license.php MIT License
 */

class Ffuenf_OrderExporter_Helper_Data extends Ffuenf_Common_Helper_Core
{
    /**
     * Path for the config for extension active status.
     */
    const CONFIG_EXTENSION_ACTIVE = 'ffuenf_orderexporter/general/enable';

    /**
     * Variable for if the extension is active.
     *
     * @var bool
     */
    protected $_bExtensionActive;

    /**
     * Check to see if the extension is active.
     *
     * @return bool
     */
    public function isExtensionActive()
    {
        return $this->getStoreFlag(self::CONFIG_EXTENSION_ACTIVE, '_bExtensionActive');
    }

    /**
     * Get compatible Magento Version
     *
     * @return bool
     */
    public function getVersion()
    {
        $m = new Mage;
        $version = $m->getVersion();
        return in_array($version, array('1.5.0.0', '1.5.0.1', '1.5.1.0', '1.6.0.0', '1.9.1.1', '1.10.0.2', '1.10.1.1', '1.11.0.0'));
    }
}
