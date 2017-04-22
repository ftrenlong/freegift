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
class Magegiant_GiantPointsBehavior_Adminhtml_Giantpoints_BehaviorController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('giantpoints/earning/behavior')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Rules Manager'), Mage::helper('adminhtml')->__('Rule Manager'));

        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('GiantPoints Behavior'))
            ->_title($this->__('Manage customer behavior earning rule'));
        $current = 'giantpoints';
        $this->getRequest()->setParam('section', $current);
        $website = $this->getRequest()->getParam('website');
        $store   = $this->getRequest()->getParam('store');

        Mage::getSingleton('adminhtml/config_data')
            ->setSection($current)
            ->setWebsite($website)
            ->setStore($store);

        $configFields = Mage::getSingleton('adminhtml/config');

        $sections    = $configFields->getSections($current);
        $section     = $sections->$current;
        $hasChildren = $configFields->hasChildren($section, $website, $store);
        if (!$hasChildren && $current) {
            $this->_redirect('*/*/', array('website' => $website, 'store' => $store));
        }
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('giantpointsbhv/adminhtml_behavior')->initForm());
        $this->_addJs($this->getLayout()->createBlock('adminhtml/template')->setTemplate('system/config/js.phtml'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        /* @var $session Mage_Adminhtml_Model_Session */
        $session = Mage::getSingleton('adminhtml/session');
        $groups  = $this->getRequest()->getPost('groups');
        try {
            $this->_saveSection();
            $section = $this->getRequest()->getParam('section');
            $website = $this->getRequest()->getParam('website');
            $store   = $this->getRequest()->getParam('store');
            Mage::getModel('adminhtml/config_data')
                ->setSection($section)
                ->setWebsite($website)
                ->setStore($store)
                ->setGroups($groups)
                ->save();

            // reinit configuration
            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();

            // website and store codes can be used in event implementation, so set them as well
            Mage::dispatchEvent("admin_system_config_changed_section_{$section}", array('website' => $website, 'store' => $store)
            );
            $session->addSuccess(Mage::helper('adminhtml')->__('The configuration has been saved.'));
        } catch (Mage_Core_Exception $e) {
            foreach (explode("\n", $e->getMessage()) as $message) {
                $session->addError($message);
            }
        } catch (Exception $e) {
            $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while saving this configuration:') . ' ' . $e->getMessage());
        }
        $this->_saveState($this->getRequest()->getPost('config_state'));
        $this->_redirect('*/*/', array('_current' => array('section', 'website', 'store')));
    }

    protected function _saveSection()
    {
        $method = '_save' . uc_words($this->getRequest()->getParam('section'), '');
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    protected function _saveState($configState = array())
    {
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        if (is_array($configState)) {
            $extra = $adminUser->getExtra();
            if (!is_array($extra)) {
                $extra = array();
            }
            if (!isset($extra['configState'])) {
                $extra['configState'] = array();
            }
            foreach ($configState as $fieldset => $state) {
                $extra['configState'][$fieldset] = $state;
            }
            $adminUser->saveExtra($extra);
        }

        return true;
    }

    public function stateAction()
    {
        if ($this->getRequest()->getParam('isAjax') == 1 && $this->getRequest()->getParam('container') != '' && $this->getRequest()->getParam('value') != '') {

            $configState = array(
                $this->getRequest()->getParam('container') => $this->getRequest()->getParam('value')
            );
            $this->_saveState($configState);
            $this->getResponse()->setBody('success');
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('giantpoints/earning/behavior');
    }

}
