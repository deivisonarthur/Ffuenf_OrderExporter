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

class Ffuenf_OrderExporter_Model_Operations_Shipment extends Mage_Core_Model_Abstract
{
    public function createShipment($orderId, $shippedItem, $date)
    {
        try {
            $order = $this->getOrderModel($orderId);
            if ($order->canShip()) {
                $shipId = Mage::getModel('sales/order_shipment_api')->create($orderId, $shippedItem, null, 0, 0);
                if ($shipId) {
                    Mage::getSingleton("sales/order_shipment")->loadByIncrementId($shipId)
                    ->setCreatedAt($date)
                    ->setUpdatedAt($date)
                    ->save()
                    ->unsetData();
                    $this->updateShipmentQTY($shippedItem);
                }
            }
            $order->unsetData();
        } catch (Exception $e) {
            Ffuenf_Common_Model_Logger::logException($e);
        }
    }

    public function updateShipmentQTY($shippedItem)
    {
        foreach ($shippedItem as $itemid => $itemqty) {
            $orderItem = Mage::getModel('sales/order_item')->load($itemid);
            $orderItem->setQtyShipped($itemqty)->save();
            $orderItem->unsetData();
        }
    }

    public function getOrderModel($lastOrderIncrementId)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($lastOrderIncrementId);
        return $order;
    }
}