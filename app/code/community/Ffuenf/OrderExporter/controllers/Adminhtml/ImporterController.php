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

class Ffuenf_OrderExporter_Adminhtml_ImporterController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('exporter/items')
             ->_addBreadcrumb(Mage::helper('adminhtml')->__('Orders Import'), Mage::helper('adminhtml')->__('Orders Import'));
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    public function importOrdersAction()
    {
        if ($_FILES['order_csv']['name'] != '') {
            $data = $this->getRequest()->getPost();
            try {
                $uploader = new Varien_File_Uploader('order_csv');
                $uploader->setAllowedExtensions(array('csv'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $path = Mage::getBaseDir('export').DS;
                $uploader->save($path, $_FILES['order_csv']['name']);
                Mage::getModel('ffuenf_orderexporter/importorders')->readCSV($path.$_FILES['order_csv']['name'], $data);
            } catch (Exception $e) {
                Mage::getModel('core/session')->addError(Mage::helper('ffuenf_orderexporter')->__('Invalid file type!!'));
            }
            $this->_redirect('*/*/');
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ffuenf_orderexporter')->__('Unable to find the import file'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * check whether the current user is allowed to access this controller
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ffuenf_orderexporter');
    }
}
