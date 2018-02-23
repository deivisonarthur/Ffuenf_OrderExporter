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

class Ffuenf_OrderExporter_Block_Adminhtml_Exporter_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'exporter';
        $this->_controller = 'adminhtml_exporter';
        $this->_updateButton('save', 'label', Mage::helper('ffuenf_orderexporter')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('ffuenf_orderexporter')->__('Delete Item'));
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('adminhtml')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save'
            ),
            -100
        );
        $this->_formScripts[] = "function toggleEditor() {
        if (tinyMCE.getInstanceById('exporter_content') == null) {
            tinyMCE.execCommand('mceAddControl', false, 'exporter_content');
            } else {
                tinyMCE.execCommand('mceRemoveControl', false, 'exporter_content');
            }
        }
        function saveAndContinueEdit(){
            editForm.submit($('edit_form').action+'back/edit/');
        }";
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('orderexporter_data') && Mage::registry('orderexporter_data')->getId()) {
            return Mage::helper('ffuenf_orderexporter')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('orderexporter_data')->getTitle()));
        } else {
            return Mage::helper('ffuenf_orderexporter')->__('Add Item');
        }
    }
}
