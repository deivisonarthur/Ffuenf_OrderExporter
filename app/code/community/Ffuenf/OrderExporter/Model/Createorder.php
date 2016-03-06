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

class Ffuenf_OrderExporter_Model_Createorder extends Mage_Core_Model_Abstract
{
    public $lastOrderIncrementId = null;
    public $orderItems = array();
    public $shippedItem = array();
    public $invoicedItem = array();
    public $creditItem = array();
    public $canceledItem = array();
    public $partialShipped = false;
    public $partialCredited = false;
    public $partialInvoiced = false;
    public $orderStatus = null;
    public $orderDetailArr = null;
    public $parentIdFlag = 0;
    public $parentId = 0;
    public $storeId = 0;
    public $invoiceCreatedAt = '';
    public $shipmentCreatedAt = '';

    protected function _construct()
    {
        $this->setLastOrderItemId();
    }

    protected function setItemData()
    {
        $order         = $this->getOrderModel($this->lastOrderIncrementId);
        $shippedItem   = array();
        $invoicedItem  = array();
        $creditItem    = array();
        $canceledItem  = array();
        $itemCount     = 0;
        $orderItems    = $this->orderItems;
        foreach ($order->getAllItems() as $item) {
            $shippedItem[$item->getItemId()] = $orderItems[$itemCount]['qty_shipped'];
            $invoicedItem[$item->getItemId()] = $orderItems[$itemCount]['qty_invoiced'];
            $creditItem[$item->getItemId()] = $orderItems[$itemCount]['qty_refunded'];
            $canceledItem[$item->getItemId()] = $orderItems[$itemCount]['qty_canceled'];
            if ($orderItems[$itemCount]['qty_shipped'] > 0) {
                $this->partialShipped = true;
            }
            if ($orderItems[$itemCount]['qty_invoiced'] > 0) {
                $this->partialInvoiced = true;
            }
            if ($orderItems[$itemCount]['qty_refunded'] > 0) {
                $this->partialCredited = true;
            }
            $itemCount++;
        }
        $this->invoicedItem = $invoicedItem;
        $this->shippedItem  = $shippedItem;
        $this->creditItem   = $creditItem;
        $this->canceledItem = $canceledItem;
    }

    protected function setGlobalData($lastOrderIncrementId, $orderItems, $salesOrderArr)
    {
        $this->lastOrderIncrementId = $lastOrderIncrementId;
        $this->orderItems           = $orderItems;
        $this->orderDetailArr       = $salesOrderArr;
        $this->orderStatus          = $salesOrderArr['order_state'];
        $this->invoiceCreatedAt     = $salesOrderArr['invoice_created_at'];
        $this->shipmentCreatedAt    = $salesOrderArr['shipment_created_at'];
        $this->setTime($lastOrderIncrementId, $salesOrderArr);
        $this->setItemData();
    }

    protected function setTime($lastOrderIncrementId, $salesOrderArr)
    {
        Mage::getModel('sales/order')->loadByIncrementId($lastOrderIncrementId)
        ->setCreatedAt($salesOrderArr['created_at'])
        ->setUpdatedAt($salesOrderArr['updated_at'])
        ->save()
        ->unsetData();
    }

    protected function setLastOrderItemId()
    {
        $resource = Mage::getSingleton('core/resource');
        $conn = $resource->getConnection('core_read');
        $results = $conn->query("SHOW TABLE STATUS LIKE '" . $resource->getTableName('sales/order_item') . "'")->fetchAll();
        foreach ($results as $data) {
            $this->parentIdFlag = (int)$data['Auto_increment'] - 1;
        }
    }

    /**
     * @return int
     */
    public function createOrder($salesOrderArr, $salesOrderItemArr, $storeId)
    {
        $this->storeId = $storeId;
        if (!$this->orderIdStatus($salesOrderArr['increment_id'])) {
            return 2;
        }
        $transaction = Mage::getSingleton('core/resource_transaction');
        $order = Mage::getModel('sales/order')
                 ->setIncrementId($salesOrderArr['increment_id'])
                 ->setStoreId($this->storeId)
                 ->setStatus($salesOrderArr['order_status'])
                 ->setHoldBeforeState($salesOrderArr['hold_before_state'])
                 ->setHoldBeforeStatus($salesOrderArr['hold_before_status'])
                 ->setIsVirtual($salesOrderArr['is_virtual'])
                 ->setBaseCurrencyCode($salesOrderArr['base_currency_code'])
                 ->setStoreCurrencyCode($salesOrderArr['store_currency_code'])
                 ->setGlobalCurrencyCode($salesOrderArr['store_currency_code'])
                 ->setOrderCurrencyCode($salesOrderArr['order_currency_code']);
        $customDetail = $this->getCustomerInfo($salesOrderArr['customer_email']);
        if ($customDetail) {
            $order->setCustomerEmail($customDetail['email'])
                  ->setCustomerFirstname($customDetail['firstname'])
                  ->setCustomerLastname($customDetail['lastname'])
                  ->setCustomerId($customDetail['entity_id'])
                  ->setCustomerGroupId($customDetail['group_id']);
        } else {
            $order->setCustomerEmail($salesOrderArr['customer_email'])
                  ->setCustomerFirstname($salesOrderArr['customer_firstname'])
                  ->setCustomerLastname($salesOrderArr['customer_lasttname'])
                  ->setCustomerIsGuest(1)
                  ->setCustomerGroupId(0);
        }
        $billingAddress = Mage::getModel('sales/order_address')
                          ->setStoreId($this->storeId)
                          ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
                          ->setCustomerAddressId($salesOrderArr['billing_address']['customer_address_id'])
                          ->setPrefix($salesOrderArr['billing_address']['prefix'])
                          ->setFirstname($salesOrderArr['billing_address']['firstname'])
                          ->setMiddlename($salesOrderArr['billing_address']['middlename'])
                          ->setLastname($salesOrderArr['billing_address']['lastname'])
                          ->setSuffix($salesOrderArr['billing_address']['suffix'])
                          ->setCompany($salesOrderArr['billing_address']['company'])
                          ->setStreet($salesOrderArr['billing_address']['street'])
                          ->setCity($salesOrderArr['billing_address']['city'])
                          ->setCountryId($salesOrderArr['billing_address']['country_id'])
                          ->setRegion($salesOrderArr['billing_address']['region'])
                          ->setPostcode($salesOrderArr['billing_address']['postcode'])
                          ->setTelephone($salesOrderArr['billing_address']['telephone'])
                          ->setFax($salesOrderArr['billing_address']['fax']);
        $order->setBillingAddress($billingAddress);
        // Set Shipping Address
        $shippingAddress = Mage::getModel('sales/order_address')
                           ->setStoreId($this->storeId)
                           ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
                           ->setCustomerAddressId($salesOrderArr['shipping_address']['customer_address_id'])
                           ->setPrefix($salesOrderArr['shipping_address']['prefix'])
                           ->setFirstname($salesOrderArr['shipping_address']['firstname'])
                           ->setMiddlename($salesOrderArr['shipping_address']['middlename'])
                           ->setLastname($salesOrderArr['shipping_address']['lastname'])
                           ->setSuffix($salesOrderArr['shipping_address']['suffix'])
                           ->setCompany($salesOrderArr['shipping_address']['company'])
                           ->setStreet($salesOrderArr['shipping_address']['street'])
                           ->setCity($salesOrderArr['shipping_address']['city'])
                           ->setCountry_id($salesOrderArr['shipping_address']['country_id'])
                           ->setRegion($salesOrderArr['shipping_address']['region'])
                           ->setPostcode($salesOrderArr['shipping_address']['postcode'])
                           ->setTelephone($salesOrderArr['shipping_address']['telephone'])
                           ->setFax($salesOrderArr['shipping_address']['fax']);
        if (!$salesOrderArr['is_virtual']) {
            $order->setShippingAddress($shippingAddress)
                  ->setShippingMethod($salesOrderArr['shipping_method'])
                  ->setShippingDescription($salesOrderArr['shipping_method']);
        }
        $orderPayment = Mage::getModel('sales/order_payment')
                        ->setStoreId($this->storeId)
                        ->setCustomerPaymentId(0)
                        ->setMethod('checkmo')
                        ->setPoNumber(' - ');
        $order->setPayment($orderPayment);
        $flag = 1;
        foreach ($salesOrderItemArr as $product) {
            $orderItem = Mage::getModel('sales/order_item')
                ->setStoreId($this->storeId)
                ->setQuoteItemId(0)
                ->setQuoteParentItemId(NULL)
                ->setSku($product['product_sku'])
                ->setProductType($product['product_type'])
                ->setProductOptions(unserialize($product['product_option']))
                ->setQtyBackordered(NULL)
                ->setTotalQtyOrdered($product['qty_ordered'])
                ->setQtyOrdered($product['qty_ordered'])
                ->setName($product['product_name'])
                ->setPrice($product['original_price'])
                ->setBasePrice($product['base_original_price'])
                ->setOriginalPrice($product['original_price'])
                ->setBaseOriginalPrice($product['base_original_price'])
                ->setRowWeight($product['row_weight'])
                ->setPriceInclTax($product['price_incl_tax'])
                ->setBasePriceInclTax($product['base_price_incl_tax'])
                ->setTaxAmount($product['product_tax_amount'])
                ->setBaseTaxAmount($product['product_base_tax_amount'])
                ->setTaxPercent($product['product_tax_percent'])
                ->setDiscountAmount($product['product_discount'])
                ->setBaseDiscountAmount($product['product_base_discount'])
                ->setDiscountPercent($product['product_discount_percent'])
                ->setRowTotal($product['row_total'])
                ->setBaseRowTotal($product['base_row_total']);
            if ($product['is_child'] == 'yes') {
                $orderItem->setParentItemId($this->parentId);
            } else if ($product['is_child'] == 'no') {
                $this->parentId = $this->parentIdFlag + $flag;
            }
            $order->addItem($orderItem);
            $flag++;
        }
        $order->setShippingAmount($salesOrderArr['shipping_amount']);
        $order->setBaseShippingAmount($salesOrderArr['base_shipping_amount']);
        //Apply Discount
        $order->setBaseDiscountAmount($salesOrderArr['base_discount_amount']);
        $order->setDiscountAmount($salesOrderArr['discount_amount']);
        //Apply Tax
        $order->setBaseTaxAmount($salesOrderArr['base_tax_amount']);
        $order->setTaxAmount($salesOrderArr['tax_amount']);
        $order->setSubtotal($salesOrderArr['subtotal'])
              ->setBaseSubtotal($salesOrderArr['base_subtotal'])
              ->setGrandTotal($salesOrderArr['grand_total'])
              ->setBaseGrandTotal($salesOrderArr['base_grand_total'])
              ->setShippingTaxAmount($salesOrderArr['shipping_tax_amount'])
              ->setBaseShippingTaxAmount($salesOrderArr['base_shipping_tax_amount'])
              ->setBaseToGlobalRate($salesOrderArr['base_to_global_rate'])
              ->setBaseToOrderRate($salesOrderArr['base_to_order_rate'])
              ->setStoreToBaseRate($salesOrderArr['store_to_base_rate'])
              ->setStoreToOrderRate($salesOrderArr['store_to_order_rate'])
              ->setSubtotalInclTax($salesOrderArr['subtotal_incl_tax'])
              ->setBaseSubtotalInclTax($salesOrderArr['base_subtotal_incl_tax'])
              ->setCouponCode($salesOrderArr['coupon_code'])
              ->setDiscountDescription($salesOrderArr['coupon_code'])
              ->setShippingInclTax($salesOrderArr['shipping_incl_tax'])
              ->setBaseShippingInclTax($salesOrderArr['base_shipping_incl_tax'])
              ->setTotalQtyOrdered($salesOrderArr['total_qty_ordered'])
              ->setRemoteIp($salesOrderArr['remote_ip']);
        $transaction->addObject($order);
        $transaction->addCommitCallback(array($order, 'place'));
        $transaction->addCommitCallback(array($order, 'save'));
        if ($transaction->save()) {
            $this->setLastOrderItemId();
            $lastOrderIncrementId = Mage::getSingleton("sales/order")->getCollection()->getLastItem()->getIncrementId();
            $this->setGlobalData($lastOrderIncrementId, $salesOrderItemArr, $salesOrderArr);
            if ($salesOrderArr['order_state'] == 'processing' || $salesOrderArr['order_state'] == 'complete') {
                return $this->setProcessing();
            }
            if ($salesOrderArr['order_state'] == 'canceled') {
                return $this->setCanceled();
            }
            if ($salesOrderArr['order_state'] == 'closed') {
                return $this->setClosed();
            }
            if ($salesOrderArr['order_state'] == 'holded') {
                return $this->setHolded();
            }
            if ($salesOrderArr['order_state'] == 'payment_review') {
                return $this->setPaymentReview();
            }
            return 1;
        } else {
            return 3;
        }
    }

    /**
     * @return int
     */
    protected function setProcessing()
    {
        if ($this->partialInvoiced) {
            $this->getInvoiceObj()->createInvoice($this->lastOrderIncrementId, $this->invoicedItem, $this->invoiceCreatedAt);
        }
        if ($this->partialShipped) {
            $this->getShipmentObj()->createShipment($this->lastOrderIncrementId, $this->shippedItem, $this->shipmentCreatedAt);
        }
        if ($this->partialCredited) {
            $this->getCreditmemoObj()->createCreditMemo($this->lastOrderIncrementId, $this->creditItem, $this->orderDetailArr);
        }
        $this->unsetAllData();
        return 1;
    }

    /**
     * @return int
     */
    protected function setHolded()
    {
        try {
            if ($this->setProcessing() == 1) {
                $order = $this->getOrderModel($this->lastOrderIncrementId);
                $order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true)->save();
                $order->unsetData();
                return 1;
            }
        } catch (Exception $e) {
            Ffuenf_Common_Model_Logger::logException($e);
            return 1;
        }
    }

    /**
     * @return int
     */
    protected function setPaymentReview()
    {
        try {
            if ($this->setProcessing() == 1) {
                $order = $this->getOrderModel($this->lastOrderIncrementId);
                $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true)->save();
                $order->unsetData();
                return 1;
            }
        } catch (Exception $e) {
            Ffuenf_Common_Model_Logger::logException($e);
            return 1;
        }
    }

    /**
     * @return int
     */
    protected function setCanceled()
    {
        try {
            if ($this->setProcessing() == 1) {
                $this->updateCanceledQTY();
                $order = $this->getOrderModel($this->lastOrderIncrementId);
                $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->save();
                $order->unsetData();
                return 1;
            }
        } catch (Exception $e) {
            Ffuenf_Common_Model_Logger::logException($e);
            return 1;
        }
    }

    /**
     * @return int
     */
    protected function setClosed()
    {
        try {
            if ($this->setProcessing() == 1) {
                $order = $this->getOrderModel($this->lastOrderIncrementId);
                $order->setStatus(Mage_Sales_Model_Order::STATE_CLOSED, true)->save();
                $order->unsetData();
                return 1;
            }
        } catch (Exception $e) {
            Ffuenf_Common_Model_Logger::logException($e);
            return 1;
        }
    }

    protected function updateCanceledQTY()
    {
        $items = $this->canceledItem;
        foreach ($items as $itemid => $itemqty) {
            $orderItem = Mage::getModel('sales/order_item')->load($itemid);
            $orderItem->setQtyCanceled($itemqty)->save();
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

    /**
     * @return bool
     */
    protected function orderIdStatus($lastOrderIncrementId)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($lastOrderIncrementId);
        if ($order->getId()) {
            return false;
        } else {
            return true;
        }
    }

    protected function unsetAllData()
    {
        $this->shippedItem = array();
        $this->invoicedItem = array();
        $this->creditItem = array();
        $this->canceledItem = array();
        $this->partialShipped = false;
        $this->partialCredited = false;
        $this->partialInvoiced = false;
        $this->orderDetailArr = false;
    }

    /**
     * @return Ffuenf_OrderExporter_Model_Operations_Invoice
     */
    protected function getInvoiceObj()
    {
        return Mage::getModel('exporter/operations_invoice');
    }

    /**
     * @return Ffuenf_OrderExporter_Model_Operations_Shipment
     */
    protected function getShipmentObj()
    {
        return Mage::getModel('exporter/operations_shipment');
    }

    /**
     * @return Ffuenf_OrderExporter_Model_Operations_Creditmemo
     */
    protected function getCreditmemoObj()
    {
        return Mage::getModel('exporter/operations_creditmemo');
    }

    /**
     * @return string|bool
     */
    protected function getCustomerInfo($email)
    {
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(Mage::getModel('core/store')->load($this->storeId)->getWebsiteId());
        if ($customer->loadByEmail($email)) {
            return $customer->getData();
        } else {
            return false;
        }
    }

    public function removeOrderStatusHistory()
    {
        $collection = Mage::getModel('sales/order_status_history')->getCollection()->addFieldToFilter('parent_id', Mage::getSingleton("sales/order")->getCollection()->getLastItem()->getId());
        foreach ($collection as $history) {
            Mage::getModel('sales/order_status_history')->load($history->getId())->delete()->save()->unsetData();
        }
    }
}
