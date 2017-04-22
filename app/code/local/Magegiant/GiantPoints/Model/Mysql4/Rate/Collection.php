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
 * Giantpoints Resource Collection Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Model_Mysql4_Rate_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('giantpoints/rate');
    }

    public function filterConversionRate($direction, $websiteId, $customerGroupId)
    {
        $this->addFieldToFilter('direction', $direction)
            ->addFieldToFilter('website_ids', array('finset' => $websiteId))
            ->addFieldToFilter('customer_group_ids', array('finset' => $customerGroupId))
            ->addFieldToFilter('points', array('gt' => 0))
            ->addFieldToFilter('money', array('gt' => 0));
        $this->getSelect()->order('priority DESC');

        return $this;

    }
}