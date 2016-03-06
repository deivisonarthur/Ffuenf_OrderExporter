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

class Ffuenf_OrderExporter_Model_Exporter extends Mage_Core_Model_Abstract
{
    /**
     * @return string
     */
    public function getPaymentMethod($order)
    {
        return $order->getPayment()->getMethod();
    }

    /**
     * @return string
     */
    public function getChildInfo($item)
    {
        if ($item->getParentItemId()) {
            return 'yes';
        } else {
            return 'no';
        }
    }

    /**
     * @return string
     */
    public function getShippingMethod($order)
    {
        if (!$order->getIsVirtual() && $order->getShippingDescription()) {
            return $order->getShippingDescription();
        } else if (!$order->getIsVirtual() && $order->getShippingMethod()) {
            return $order->getShippingMethod();
        }
        return '';
    }

    /**
     * @return string
     */
    public function getItemSku($item)
    {
        if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return $item->getProductOptionByCode('simple_sku');
        }
        return $item->getSku();
    }

    /**
     * @return string
     */
    public function formatText($string)
    {
        $string = str_replace(',', ' ', $string);
        return $string;
    }

    /**
     * @return array
     */
    public function getStoreIds()
    {
        $collection = Mage::getModel('core/store')->getCollection();
        $storeIds = array();
        $i = 0;
        foreach ($collection as $data) {
            $storeIds[$i]['label'] = $data['name'];
            $storeIds[$i]['value'] = $data['store_id'];
            $i++;
        }
        return $storeIds;
    }

    /**
     * @return array
     */
    public function getCreditMemoDetail($order)
    {
        $creditDetail = array();
        $creditDetail['adjustment_positive'] = 0;
        $creditDetail['adjustment_negative'] = 0;
        $creditDetail['shipping_amount'] = 0;
        $creditDetail['base_shipping_amount'] = 0;
        $creditDetail['subtotal'] = 0;
        $creditDetail['base_subtotal'] = 0;
        $creditDetail['tax_amount'] = 0;
        $creditDetail['base_tax_amount'] = 0;
        $creditDetail['discount_amount'] = 0;
        $creditDetail['base_discount_amount'] = 0;
        $collection = $order->getCreditmemosCollection();
        if (count($collection)) {
            foreach ($collection as $data) {
                $creditDetail['adjustment_positive'] += $data->getData('adjustment_positive');
                $creditDetail['adjustment_negative'] += $data->getData('adjustment_negative');
                $creditDetail['shipping_amount'] += $data->getData('shipping_amount');
                $creditDetail['base_shipping_amount'] += $data->getData('base_shipping_amount');
                $creditDetail['subtotal'] += $data->getData('subtotal');
                $creditDetail['base_subtotal'] += $data->getData('base_subtotal');
                $creditDetail['tax_amount'] += $data->getData('tax_amount');
                $creditDetail['base_tax_amount'] += $data->getData('base_tax_amount');
                $creditDetail['discount_amount'] += $data->getData('discount_amount');
                $creditDetail['base_discount_amount'] += $data->getData('base_discount_amount');
            }
        }
        return $creditDetail;
    }

    /**
     * @return string
     */
    public function getInvoiceDate($order)
    {
        $date = '';
        $collection = $order->getInvoiceCollection();
        if (count($collection)) {
            foreach ($collection as $data) {
                $date = $data->getData('created_at');
            }
        }
        return $date;
    }

    /**
     * @return string
     */
    public function getShipmentDate($order)
    {
        $date = '';
        $collection = $order->getShipmentsCollection();
        if (count($collection)) {
            foreach ($collection as $data) {
                $date = $data->getData('created_at');
            }
        }
        return $date;
    }

    /**
     * @return string
     */
    public function getCreditmemoDate($order)
    {
        $date = '';
        $collection = $order->getCreditmemosCollection();
        if (count($collection)) {
            foreach ($collection as $data) {
                $date = $data->getData('created_at');
            }
        }
        return $date;
    }
}