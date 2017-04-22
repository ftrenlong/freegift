<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Giantpoints Adminhtml Controller
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPointsRefer_Adminhtml_Giantpoints_Refer_InvitationController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('giantpoints/referral/history')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Invitation History'),
                Mage::helper('adminhtml')->__('Invitation History')
            );

        return $this;
    }

    /**
     * index action
     */
    public function indexAction()
    {
        $this->_title($this->__('Reward Points'))
            ->_title($this->__('Invitation History'));
        $this->_initAction()
            ->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * mass delete action
     */
    public function massDeleteAction()
    {
        try {
            $rates = $this->getRequest()->getParam('rate_ids');
            foreach ($rates as $rate) {
                Mage::getModel('giantpoints/rate')->load($rate)->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('giantpoints')->__('Total of %d record(s) were successfully removed', count($rates))
            );
        } catch (Exception $exc) {
            Mage::getSingleton('adminhtml/session')->addError($exc->getMessage());
        }
        $this->_redirect('*/*/index');
    }

    /**
     * delete item action
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('giantpoints/rate');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Earning rate was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('giantpoints/referral/history');
    }
}