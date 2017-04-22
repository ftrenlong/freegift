<?php

class Magegiant_GiantPointsRefer_Model_Rule_Action_Milestone_Referrals extends Magegiant_GiantPointsMilestone_Model_Behavior_Rule_Action_Abstract implements Magegiant_GiantPoints_Model_Rule_Action_Interface, Magegiant_GiantPointsMilestone_Model_Behavior_Rule_Action_Interface
{
    const ACTION_CODE = 'giantpoints_customer_milestone_referrals';

    /**
     * @return string
     */
    public function getConditionLabel()
    {
        return Mage::helper('giantpoints')->__('[Milestone] - Reach number of referrals');
    }

    /**
     * Get label for field added
     *
     * @return string
     */
    public function getFieldLabel()
    {
        return Mage::helper('giantpoints')->__('Number of Referrals');
    }

    /**
     * Add comment for added field
     *
     * @return string
     */
    public function getFieldComments()
    {
        if (Mage::helper('milestone/config')->getReferralsTrigger() == Magegiant_GiantPointsMilestone_Model_System_Config_Source_Referral_Status::REFERRAL_SIGNUP)
            return Mage::helper('giantpoints')->__('This counts when Referrals Signup');

        return Mage::helper('giantpoints')->__('This counts when Referral Places First Order');
    }

    /**
     * Get condition code
     *
     * @return string
     */
    public function getConditionCode()
    {
        return self::ACTION_CODE;
    }
}