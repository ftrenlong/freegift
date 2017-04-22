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
 * Giantpoints Resource Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPointsRefer_Model_Mysql4_Invitation extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('giantpointsrefer/invitation', 'invitation_id');
        $this->_read = $this->_getReadAdapter();
    }

    /**
     * Get entity id from database by email address
     *
     * @param _Model_Invitation $invitation
     * @param string            $emailAddress
     * @param int               $status
     *
     * @return integer
     */
    public function loadByEmailAndStatus($invitation, $emailAddress, $status)
    {
        $select = $this->_read->select()
            ->from($this->getTable('giantpointsrefer/invitation'))
            ->where("email=? ", $emailAddress)
            ->where("status=? ", $status);

        if ($data = $this->_read->fetchRow($select)) {
            $invitation->addData($data);
        }
        $this->_afterLoad($invitation);

        return $this;
    }

    /**
     * Get entity id from database by email address
     *
     * @param string $emailAddress
     * @param int    $storeId
     *
     */
    public function loadByEmailAndStore($invitation, $emailAddress, $storeId)
    {
        $select = $this->_read->select()
            ->from($this->getTable('giantpointsrefer/invitation'))
            ->where("email=? ", $emailAddress)
            ->where("store_id=? ", $storeId);

        if ($data = $this->_read->fetchRow($select)) {
            $invitation->addData($data);
        }
        $this->_afterLoad($invitation);

        return $this;
    }

    /**
     * Load invitation data  from DB by email
     *
     * @param string $emailAddress
     *
     */
    public function loadByEmail($invitation, $emailAddress)
    {
        $select = $this->_read->select()
            ->from($this->getTable('giantpointsrefer/invitation'))
            ->where('email=?', $emailAddress);

        if ($data = $this->_read->fetchRow($select)) {
            $invitation->addData($data);
        }
        $this->_afterLoad($invitation);

        return $this;
    }

    /**
     * Load invitation data  from DB by referral id
     *
     * @param int $referralId
     *
     */
    public function loadByReferralId($invitation, $referralId)
    {
        $select = $this->_read->select()
            ->from($this->getTable('giantpointsrefer/invitation'))
            ->where("referral_id=?", $referralId);

        if ($data = $this->_read->fetchRow($select)) {
            $invitation->addData($data);
        }
        $this->_afterLoad($invitation);

        return $this;
    }
}