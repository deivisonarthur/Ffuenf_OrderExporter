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

class Ffuenf_OrderExporter_Model_Operations_Creditmemo extends Mage_Core_Model_Abstract
{
    public function createCreditMemo($orderId, $creditItem, $creditDetail)
    {
        $order = $this->getOrderModel($orderId);
        try {
            $data = array('qtys' => $creditItem, 'shipping_amount' => $creditDetail['refunded_shipping_amount'],
            'adjustment_positive'=>$creditDetail['adjustment_positive'], 'adjustment_negative' => $creditDetail['adjustment_negative']);
            if (Mage::helper('ffuenf_orderexporter')->getVersion()) {
                $service = Mage::getModel('sales/service_order', $order);
                $creditMemo = $service->prepareCreditmemo($data);
                $creditMemo->setState(2)->save();
                $this->updateStatus($orderId, $creditDetail);
            } else {
                Mage::getModel('sales/order_creditmemo_api')->create($orderId, $data, null, 0, 0);
            }
            $model = Mage::getSingleton("sales/order_creditmemo");
            $creditId = $model->getCollection()->getLastItem()->getId();
            $model->load($creditId)
                  ->setCreatedAt($creditDetail['creditmemo_created_at'])
                  ->setUpdatedAt($creditDetail['creditmemo_created_at'])
                  ->save()
                  ->unsetData();
            $this->updateCreditQTY($creditItem);
        } catch (Exception $e) {
            Ffuenf_Common_Model_Logger::logException($e);
        }
        $order->unsetData();
    }

    public function updateCreditQTY($creditItem)
    {
        foreach ($creditItem as $itemid => $itemqty) {
            $orderItem = Mage::getModel('sales/order_item')->load($itemid);
            $orderItem->setQtyRefunded($itemqty)->save();
            $orderItem->unsetData();
        }
    }

    public function updateStatus($orderId, $refunded)
    {
        $order = $this->getOrderModel($orderId);
        //set creditmemo data
        $order->setSubtotalRefunded($refunded['refunded_subtotal'])
            ->setBaseSubtotalRefunded($refunded['refunded_subtotal'])
            ->setTaxRefunded($refunded['refunded_tax_amount'])
            ->setBaseTaxRefunded($refunded['base_refunded_tax_amount'])
            ->setDiscountRefunded($refunded['refunded_discount_amount'])
            ->setBaseDiscountRefunded($refunded['base_refunded_discount_amount'])
            ->setShippingRefunded($refunded['refunded_shipping_amount'])
            ->setBaseShippingRefunded($refunded['base_refunded_shipping_amount'])
            ->setTotalRefunded($refunded['total_refunded'])
            ->setBaseTotalRefunded($refunded['base_total_refunded'])
            ->setTotalOfflineRefunded($refunded['total_refunded'])
            ->setBaseTotalOfflineRefunded($refunded['base_total_refunded'])
            ->setAdjustmentNegative($refunded['adjustment_positive'])
            ->setBaseAdjustmentNegative($refunded['adjustment_positive'])
            ->setAdjustmentPositive($refunded['adjustment_negative'])
            ->setBaseAdjustmentPositive($refunded['adjustment_negative'])
            ->save();
        $order->unsetData();
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