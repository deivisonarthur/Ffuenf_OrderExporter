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

class Ffuenf_OrderExporter_Model_Operations_Invoice extends Mage_Core_Model_Abstract
{
    public function createInvoice($orderId, $invoiceItem, $date)
    {
        try {
            $order = $this->getOrderModel($orderId);
            if ($order->canInvoice()) {
                $invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), $invoiceItem, null, 0, 0);
                if ($invoiceId) {
                    Mage::getSingleton("sales/order_invoice")->loadByIncrementId($invoiceId)
                    ->setCreatedAt($date)
                    ->setUpdatedAt($date)
                    ->save()
                    ->unsetData();
                    $this->updateInvoiceQTY($invoiceItem);
                }
            }
            $order->unsetData();
        } catch (Exception $e) {
            Ffuenf_Common_Model_Logger::logException($e);
        }
    }

    public function updateInvoiceQTY($invoiceItem)
    {
        foreach ($invoiceItem as $itemid => $itemqty) {
            $orderItem = Mage::getModel('sales/order_item')->load($itemid);
            $orderItem->setQtyInvoiced($itemqty)->save();
            $orderItem->unsetData();
        }
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrderModel($lastOrderIncrementId)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($lastOrderIncrementId);
        return $order;
    }
}
