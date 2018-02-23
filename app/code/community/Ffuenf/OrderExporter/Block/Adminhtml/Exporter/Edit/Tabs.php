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

class Ffuenf_OrderExporter_Block_Adminhtml_Exporter_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('exporter_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ffuenf_orderexporter')->__('Import Orders'));
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_section',
            array(
                'label'   => Mage::helper('ffuenf_orderexporter')->__('Import Orders'),
                'title'   => Mage::helper('ffuenf_orderexporter')->__('Import Orders'),
                'content' => $this->getLayout()->createBlock('exporter/adminhtml_exporter_edit_tab_form')->toHtml()
                    )
        );
        return parent::_beforeToHtml();
    }
}
