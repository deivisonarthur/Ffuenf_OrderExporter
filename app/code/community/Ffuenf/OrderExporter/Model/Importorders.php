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

class Ffuenf_OrderExporter_Model_Importorders extends Mage_Core_Model_Abstract
{
    public $orderInfo     = array();
    public $orderItemInfo = array();
    public $orderItemFlag = 0;
    public $storeId       = 0;
    public $importLimit   = 0;

    public function readCSV($csvFile, $data)
    {
        $this->importLimit = $data['importLimit'];
        $this->storeId     = $data['storeId'];
        $fileHandle        = fopen($csvFile, 'r');
        $i                 = 0;
        $decline           = false;
        $success           = 0;
        $parentFlag        = 0;
        $lineNumber        = 2;
        $totalOrder        = 0;
        $lineOfText        = array();
        while (!feof($fileHandle)) {
            $lineOfText[] = fgetcsv($fileHandle);
            if ($i != 0) {
                if ($lineOfText[$i][0] != '' && $parentFlag == 0) {
                    $this->insertOrderData($lineOfText[$i]);
                    $parentFlag = 1;
                    $totalOrder++;
                } else if ($lineOfText[$i][91] != '' && $parentFlag == 1 && $lineOfText[$i][0] == '') {
                    $this->insertOrderItem($lineOfText[$i]);
                } else if ($parentFlag == 1) {
                    try {
                        $message = Mage::getModel('exporter/createorder')->createOrder($this->orderInfo, $this->orderItemInfo, $this->storeId);
                        Mage::getModel('exporter/createorder')->removeOrderStatusHistory();
                    } catch (Exception $e) {
                        Ffuenf_Common_Model_Logger::logException($e);
                        $decline = true;
                        $message = 0;
                    }
                    if ($message == 1) {
                        $success++;
                    }
                    if ($message == 2) {
                        Ffuenf_Common_Model_Logger::logSystem(
                            array(
                                'timestamp' => 'datetime',
                                'extension' => 'Ffuenf_OrderExporter',
                                'type'      => 'debug',
                                'message'   => "<p><strong>Order Id:</strong>" . $this->orderInfo['increment_id'] . "</p>
                                                <p><strong>Error Message:</strong> Order id already exist</p>
                                                <p><strong>Line Number:</strong>" . $lineNumber . "</p>"
                            )
                        );
                        $decline = true;
                    }
                    $this->orderInfo = array();
                    $this->orderItemInfo = array();
                    $this->orderItemFlag = 0;
                    $this->insertOrderData($lineOfText[$i]);
                    $parentFlag = 1;
                    $lineNumber = $i + 1;
                    $totalOrder++;
                }
            }
            $i++;
            if ($this->importLimit < $totalOrder) {
                break;
            }
        }
        if ($success) {
            Mage::getModel('core/session')->addSuccess(Mage::helper('ffuenf_orderexporter')->__('Total ' . $success . ' order(s) imported successfully!'));
        }
        if ($decline) {
            Mage::getModel('core/session')->addError(Mage::helper('ffuenf_orderexporter')->__('Click <a href="' . Mage::helper("adminhtml")->getUrl("log_system") . '">here</a> to view the error log'));
        }
        fclose($fileHandle);
        return array($success, $decline);
    }

    public function insertOrderData($ordersData)
    {
        $salesOrderArr     = array();
        $salesOrderItemArr = array();
        $salesOrder        = $this->getSalesTable();
        $salesPayment      = $this->getSalesPayment();
        $salesShipping     = $this->getSalesBilling();
        $salesBilling      = $this->getSalesBilling();
        $salesOrderItem    = $this->getSalesItem();
        $i = 0;
        $j = 0;
        $k = 0;
        $l = 0;
        $m = 0;
        foreach ($ordersData as $order) {
            if (count($salesOrder) > $i) {
                $salesOrderArr[$salesOrder[$i]] = $order;
            } else if (count($salesBilling) > $j) {
                $salesBilling[$j] . $salesOrderArr['billing_address'][$salesBilling[$j]] = $order;
                $j++;
            } else if (count($salesShipping) > $k) {
                $salesOrderArr['shipping_address'][$salesShipping[$k]] = $order;
                $k++;
            } else if (count($salesPayment) > $l) {
                $salesOrderArr['payment'][$salesPayment[$l]] = $order;
                $l++;
            } else if (count($salesOrderItem) > $m) {
                $salesOrderItemArr[$salesOrderItem[$m]] = $order;
                $m++;
            }
            $i++;
        }
        $this->orderInfo = $salesOrderArr;
        $this->orderItemInfo[$this->orderItemFlag] = $salesOrderItemArr;
        $this->orderItemFlag++;
    }

    public function insertOrderItem($ordersData)
    {
        $salesOrderItemArr = array();
        $salesOrderItem = $this->getSalesItem();
        $i = 0;
        $count = count($ordersData);
        for ($j = 91; $j < $count; $j++) {
            if ($count > $i) {
                $salesOrderItemArr[$salesOrderItem[$i]] = $ordersData[$j];
            }
            $i++;
        }
        $this->orderItemInfo[$this->orderItemFlag] = $salesOrderItemArr;
        $this->orderItemFlag++;
    }

    public function getSalesTable()
    {
        return array(
            'increment_id',
            'customer_email',
            'customer_firstname',
            'customer_lasttname',
            'customer_prefix',
            'customer_middlename',
            'customer_suffix',
            'taxvat',
            'created_at',
            'updated_at',
            'invoice_created_at',
            'shipment_created_at',
            'creditmemo_created_at',
            'tax_amount',
            'base_tax_amount',
            'discount_amount',
            'base_discount_amount',
            'shipping_tax_amount',
            'base_shipping_tax_amount',
            'base_to_global_rate',
            'base_to_order_rate',
            'store_to_base_rate',
            'store_to_order_rate',
            'subtotal_incl_tax',
            'base_subtotal_incl_tax',
            'coupon_code',
            'shipping_incl_tax',
            'base_shipping_incl_tax',
            'shipping_method',
            'shipping_amount',
            'subtotal',
            'base_subtotal',
            'grand_total',
            'base_grand_total',
            'base_shipping_amount',
            'adjustment_positive',
            'adjustment_negative',
            'refunded_shipping_amount',
            'base_refunded_shipping_amount',
            'refunded_subtotal',
            'base_refunded_subtotal',
            'refunded_tax_amount',
            'base_refunded_tax_amount',
            'refunded_discount_amount',
            'base_refunded_discount_amount',
            'storeId',
            'order_status',
            'order_state',
            'hold_before_state',
            'hold_before_status',
            'store_currency_code',
            'base_currency_code',
            'order_currency_code',
            'total_paid',
            'base_total_paid',
            'is_virtual',
            'total_qty_ordered',
            'remote_ip',
            'total_refunded',
            'base_total_refunded',
            'total_canceled',
            'total_invoiced'
        );
    }

    public function getSalesBilling()
    {
        return array(
            'customer_address_id',
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',
            'street',
            'city',
            'region',
            'country_id',
            'postcode',
            'telephone',
            'company',
            'fax'
        );
    }

    public function getSalesPayment()
    {
        return array('method');
    }

    public function getSalesItem()
    {
        return array(
            'product_sku',
            'product_name',
            'qty_ordered',
            'qty_invoiced',
            'qty_shipped',
            'qty_refunded',
            'qty_canceled',
            'product_type',
            'original_price',
            'base_original_price',
            'row_total',
            'base_row_total',
            'row_weight',
            'price_incl_tax',
            'base_price_incl_tax',
            'product_tax_amount',
            'product_base_tax_amount',
            'product_tax_percent',
            'product_discount',
            'product_base_discount',
            'product_discount_percent',
            'is_child',
            'product_option'
        );
    }
}