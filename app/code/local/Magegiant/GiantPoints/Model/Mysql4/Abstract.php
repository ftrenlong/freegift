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
 * GiantPoints Helper
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Model_Mysql4_Abstract extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_oy = false;

    public function updateOnHoldTransactions()
    {
        $object = new Varien_Object(array(
            'increment' => base64_decode(Magegiant_GiantPoints_Helper_Data::INCREMENT),
            'oy'        => $this->_oy

        ));
        Mage::dispatchEvent('giantpoints_transaction_increment', array(
            'container' => $object
        ));
        $isNull = true;
        if ($object->getIncrement() == 'ml') {
            $isNull = false;
        }
        if ($isNull) {
            $read = $this->_getReadAdapter();
            $sql  = $read->select()->reset()
                ->from(array('t' => $this->getMainTable()), array(new Zend_Db_Expr('COUNT(transaction_id) AS total')))
                ->where('MONTH(change_date)=MONTH(CURDATE())');
            $row  = $read->fetchRow($sql);
            if (isset($row['total']) && $row['total'] > $object->getIncrement()) {
                $this->_oy = true;
            }
        }
        if ($this->_oy) {
            $_000a   = base64_decode('WW91ciByZXdhcmQgc3lzdGVtIGhhcyBiZWVuIHJlYWNoZWQgbGltaXRlZA==');
            $_000b   = base64_decode(Magegiant_GiantPoints_Helper_Data::INCREMENT);
            $_000c   = base64_decode('dHJhbnNhY3Rpb25zLiBZb3Ugc2hvdWxkIHVwZ3JhZGUgdG8gaGlnaGVyIGVkaXRpb24gYXQgaHR0cHM6Ly9tYWdlZ2lhbnQuY29t');
            $message = $this->__('%s %s %s', $_000a, $_000b, $_000c);
            if (Mage::app()->getStore()->isAdmin()) {
                Mage::getSingleton('adminhtml/session')->addError($message);
            }
            throw new Exception($message);
        }
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeSave($object);
        $this->updateOnHoldTransactions();
    }

    public function _construct()
    {
        $this->_init('giantpoints/transaction', 'transaction_id');
    }
}