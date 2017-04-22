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
class Magegiant_GiantPointsRefer_Block_Adminhtml_Salesrule_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    const PAGE_TABS_BLOCK_ID = 'rule_tabs';

    public function __construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'giantpointsrefer';
        $this->_controller = 'adminhtml_salesrule';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('giantpointsrefer')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('giantpointsrefer')->__('Delete Rule'));
        $this->_addButton(
            'save_and_continue',
            array(
                'label'   => Mage::helper('giantpointsrefer')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
                'class'   => 'save'
            ),
            10
        );
        $this->_formScripts[] = "
            function saveAndApply(){
                editForm.submit($('edit_form').action+'apply/1/');
            }
            function saveAndContinueEdit(urlTemplate){
                var urlTemplateSyntax = /(^|.|\\r|\\n)({{(\\w+)}})/;
                var template = new Template(urlTemplate, urlTemplateSyntax);
                var url = template.evaluate({tab_id:" . self::PAGE_TABS_BLOCK_ID . "JsTabs.activeTab.id});
                editForm.submit(url);
            }
        ";
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'   => true,
            'back'       => 'edit',
            'tab'        => '{{tab_id}}',
            'active_tab' => null
        ));
    }

    public function getHeaderText()
    {
        $rule = Mage::registry('refer_salesrule_data');
        if ($rule->getId()) {
            return Mage::helper('giantpointsrefer')->__("Edit Refer '%s'", $this->escapeHtml($rule->getName()));
        } else {
            return Mage::helper('giantpointsrefer')->__('New Rule');
        }
    }

    protected function _getSalesRule()
    {
        return Mage::registry('refer_salesrule_data');
    }
}