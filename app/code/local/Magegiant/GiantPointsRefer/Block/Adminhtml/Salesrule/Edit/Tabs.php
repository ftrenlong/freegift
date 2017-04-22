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
 * Giantpoints Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPointsRefer_Block_Adminhtml_Salesrule_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('rule_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('giantpoints')->__('Refer Friends Rule'));
    }

    protected function _beforeToHtml()
    {
        $helper = Mage::helper('giantpoints');

        $this->addTab(
            'main_section',
            array(
                'label'   => $helper->__('General'),
                'title'   => $helper->__('General'),
                'content' => $this->getLayout()->createBlock('giantpointsrefer/adminhtml_salesrule_edit_tab_main')->toHtml(),
                'active'  => true,
            )
        );

        $this->addTab(
            'conditions_section',
            array(
                'label'   => $helper->__('Conditions'),
                'title'   => $helper->__('Conditions'),
                'content' => $this->getLayout()->createBlock('giantpointsrefer/adminhtml_salesrule_edit_tab_conditions')->toHtml(),
            )
        );

        $this->addTab(
            'actions_section',
            array(
                'label'   => $helper->__('Actions'),
                'title'   => $helper->__('Actions'),
                'content' => $this->getLayout()->createBlock('giantpointsrefer/adminhtml_salesrule_edit_tab_actions')->toHtml(),
            )
        );
        $this->_updateActiveTab();

        return parent::_beforeToHtml();
    }

    protected function _updateActiveTab()
    {
        $tabId = $this->getRequest()->getParam('tab');
        if ($tabId) {
            $tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
            if ($tabId) {
                $this->setActiveTab($tabId);
            }
        }
    }
}