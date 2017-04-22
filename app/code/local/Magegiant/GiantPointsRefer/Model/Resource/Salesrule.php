<?php
/**
 * MageGiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageGiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Giantgiantpoints Resource Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPointsRefer_Model_Resource_Salesrule extends Magegiant_GiantPointsRefer_Model_Rule_Resource_Abstract
{
    public function _construct()
    {
        $this->_init('giantpointsrefer/salesrule', 'rule_id');
    }

    public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (is_array($object->getData('customer_group_ids'))) {
            $object->setData('customer_group_ids', implode(',', $object->getData('customer_group_ids')));
        }
        if (is_array($object->getData('referrals_group_ids'))) {
            $object->setData('referrals_group_ids', implode(',', $object->getData('referrals_group_ids')));
        }
        if (is_array($object->getData('website_ids'))) {
            $object->setData('website_ids', implode(',', $object->getData('website_ids')));
        }
        if (!$object->getFromDate()) {
            $object->setFromDate(Mage::app()->getLocale()->date());
        }
        if ($object->getFromDate() instanceof Zend_Date) {
            $object->setFromDate($object->getFromDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        }

        if (!$object->getToDate()) {
            $object->setToDate(new Zend_Db_Expr('NULL'));
        } else {
            if ($object->getToDate() instanceof Zend_Date) {
                $object->setToDate($object->getToDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
            }
        }
    }
}