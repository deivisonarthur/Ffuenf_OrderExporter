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
 * @copyright  Copyright (c) 2016 ffuenf (http://www.ffuenf.de)
 * @license    http://opensource.org/licenses/mit-license.php MIT License
 */

class Ffuenf_OrderExporter_Block_Sales_Order_Form_Form extends Mage_Adminhtml_Block_Sales_Order_Shipment_View_Form
{
    /**
     * @return bool|string
     */
    public function canCreateShippingLabel()
    {
        $shippingCarrier = $this->getOrder()->getShippingCarrier();
        if ($shippingCarrier) {
            return $shippingCarrier && $shippingCarrier->isShippingLabelsAvailable();
        } else {
            return $shippingCarrier;
        }
    }
}
