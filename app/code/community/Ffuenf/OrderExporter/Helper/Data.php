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

class Ffuenf_OrderExporter_Helper_Data extends Ffuenf_Common_Helper_Core
{
    const CONFIG_EXTENSION_ACTIVE               = 'ffuenf_orderexporter/general/enable';
    const CONFIG_EXTENSION_LOG_SYSTEM_ACTIVE    = 'ffuenf_orderexporter/log/enable';
    const CONFIG_EXTENSION_LOG_PROFILE_ACTIVE   = 'ffuenf_orderexporter/log/profile_enable';
    const CONFIG_EXTENSION_LOG_EXCEPTION_ACTIVE = 'ffuenf_orderexporter/log/exception_enable';

    /**
     * Check to see if the extension is active.
     *
     * @return bool
     */
    public function isExtensionActive()
    {
	return Mage::getStoreConfigFlag(self::CONFIG_EXTENSION_ACTIVE);
    }

    /**
     * Check to see if logging is active.
     *
     * @return bool
     */
    public function isLogActive()
    {
	return Mage::getStoreConfigFlag(self::CONFIG_EXTENSION_LOG_SYSTEM_ACTIVE);
    }

    /**
     * Check to see if profile logging is active.
     *
     * @return bool
     */
    public function isLogProfileActive()
    {
	return Mage::getStoreConfigFlag(self::CONFIG_EXTENSION_LOG_PROFILE_ACTIVE);
    }

    /**
     * Check to see if exception logging is active.
     *
     * @return bool
     */
    public function isLogExceptionActive()
    {
	return Mage::getStoreConfigFlag(self::CONFIG_EXTENSION_LOG_EXCEPTION_ACTIVE);
    }
}
