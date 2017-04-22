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
 * Giantgiantpoints Resource Collection Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPointsRefer_Model_Resource_Salesrule_Collection extends Magegiant_GiantPointsRefer_Model_Rule_Resource_Collection_Abstract
{

    public function _construct()
    {
        $this->_init('giantpointsrefer/salesrule');
    }

    public function addAvailableFilter($date = null)
    {
        if (is_null($date))
            $date = Mage::getModel('core/date')->gmtDate('Y-m-d');
        $this->getSelect()
            ->where('is_active = ?', 1)
            ->where('date(from_date) <= ?', $date)
            ->where('date(to_date) >= ? OR to_date is null', $date);

        return $this;
    }

    /**
     * get Rules by Ids
     *
     * @param $ids
     * @return $this
     */
    public function getRulesByIds($ids)
    {
        if (!is_array($ids)) {
            $ids = new Zend_Db_Expr($ids);
        }
        $this->getSelect()
            ->where('main_table.rule_id in (?)', $ids);

        return $this;
    }

    public function addFilterByCustomerGroup($customerGroupId)
    {
        $this->getSelect()
            ->where('FIND_IN_SET(?, customer_group_ids)', $customerGroupId);

        return $this;
    }

    public function addFilterByReferralsGroup($groupId)
    {
        $this->getSelect()
            ->where('FIND_IN_SET(?, referrals_group_ids)', $groupId);

        return $this;
    }

    public function addFilterByWebsiteId($websiteId)
    {
        $this->getSelect()
            ->where('FIND_IN_SET(?, website_ids)', $websiteId);

        return $this;
    }

    public function addFilterByType($type)
    {
        $this->getSelect()
            ->where('rule_type=?', $type);

        return $this;
    }

    /**
     * Filter collection by website(s), customer group(s) and date.
     * Filter collection to only active rules.
     * Sorting is not involved
     *
     * @param int         $websiteId
     * @param int         $customerGroupId
     * @param string|null $now
     * @use $this->addWebsiteFilter()
     *
     * @return Mage_SalesRule_Model_Mysql4_Rule_Collection
     */
    public function addWebsiteGroupDateFilter($websiteId, $customerGroupId, $now = null)
    {
        if (!$this->getFlag('website_group_date_filter')) {
            if (is_null($now)) {
                $now = Mage::getModel('core/date')->date('Y-m-d');
            }

            $this->addWebsiteFilter($websiteId);

            $entityInfo = $this->_getAssociatedEntityInfo('customer_group');
            $connection = $this->getConnection();
            $this->getSelect()
                ->joinInner(
                    array('customer_group_ids' => $this->getTable($entityInfo['associations_table'])),
                    $connection->quoteInto(
                        'main_table.' . $entityInfo['rule_id_field']
                        . ' = customer_group_ids.' . $entityInfo['rule_id_field']
                        . ' AND customer_group_ids.' . $entityInfo['entity_id_field'] . ' = ?',
                        (int)$customerGroupId
                    ),
                    array()
                )
                ->where('from_date is null or from_date <= ?', $now)
                ->where('to_date is null or to_date >= ?', $now);

            $this->addIsActiveFilter();

            $this->setFlag('website_group_date_filter', true);
        }

        return $this;
    }

}
