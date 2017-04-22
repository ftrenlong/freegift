<?php

class Magegiant_GiantPointsBehavior_Model_Rule_Action_Customer_Referral_Order extends Magegiant_GiantPoints_Model_Rule_Action_Abstract implements Magegiant_GiantPoints_Model_Rule_Action_Interface
{

    const ACTION_REFERRAL_ORDER = 'giantpoints_customer_referral_order';

    public function _construct()
    {
        $this->setCaption("Customer Referral");
        $this->setDescription("Customer will get points for every purchase made by a referred customer.");
        $this->setCode(self::ACTION_REFERRAL_ORDER);

        return parent::_construct();
    }

    public function getCustomerConditions()
    {
        return array(
            self::ACTION_REFERRAL_ORDER => Mage::helper('giantpoints')->__('Referral makes any order'),
        );
    }

    public function visitAdminActions(&$fieldset)
    {
        $fieldset->addField('simple_action', 'select',
            array(
                'name'    => 'simple_action',
                'label'   => 'Award Points as:',
                // 'required' => true,
                'options' => array(
                    'by_percent' => Mage::helper('giantpoints')->__("% of Points Earned By Referral"),
                    'by_fixed'   => Mage::helper('giantpoints')->__("Fixed Amount")
                )
            ), 'point_action');

        return $this;
    }

    public function getNewActions()
    {
        return array();
    }

    public function getAdminFormScripts()
    {
        return array();
    }

    public function getAdminFormInitScripts()
    {
        $hidescript = "
            function checkReferralFields() {

        	    var rule_simple_action_row = $('rule_simple_action').up().up();
        	    var rule_point_amount_row = $('rule_point_amount').up().up();
        	    
                var v = $('rule_points_conditions').value;
                if(v == 'customer_referral_order') {
                    rule_simple_action_row.show(); 
	                simple_action = $('rule_simple_action').value;
	                if(simple_action == 'by_percent') {
	                    rule_point_amount_row.cells[0].down().innerHTML = '{$this->getPercentageCaption()}';
	                } else {
	                    rule_point_amount_row.cells[0].down().innerHTML = '{$this->getDefaultCaption()}';				
	                }
	                    	
                } else {
                	rule_simple_action_row.hide();
                	rule_point_amount_row.cells[0].down().innerHTML = '{$this->getDefaultCaption()}';                                   				
                }
                
            }
    			
    	   	// update the onchange events for the rule_points_conditions field.
    	   	document.observe('dom:loaded', function() {
        	   	var old_cond_onchange_event = $('rule_points_conditions').getAttribute('onchange');
        		$('rule_points_conditions').setAttribute('onchange', (old_cond_onchange_event == null ? '' : old_cond_onchange_event) + 'checkReferralFields();');
                
        	   	var old_sa_onchange_event = $('rule_simple_action').getAttribute('onchange');
        		$('rule_simple_action').setAttribute('onchange', (old_sa_onchange_event == null ? '' : old_sa_onchange_event) + 'checkReferralFields();');
    		});
    			
            checkReferralFields();
        ";

        return array($hidescript);
    }

    protected function getPercentageCaption()
    {
        return (Mage::helper('giantpoints')->__("% of Points Earned By Referral") . "<span class=\"required\">*</span>:");
    }

    protected function getDefaultCaption()
    {
        return Mage::helper('giantpoints')->__("Fixed Amount") . "<span class=\"required\">*</span>:";
    }

}