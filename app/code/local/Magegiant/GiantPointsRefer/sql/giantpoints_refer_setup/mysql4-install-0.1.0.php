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
 * @package     MageGiant_GiantPointsRefer
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create giantpointsrefer table
 */
$installer->run("
  DROP TABLE IF EXISTS {$this->getTable('giantpointsrefer/invitation')};
  CREATE TABLE {$this->getTable('giantpointsrefer/invitation')} (
        `invitation_id` INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        `date` DATETIME NOT NULL ,
        `email` VARCHAR( 255 ) NOT NULL ,
        `referral_id` INT( 10 ) UNSIGNED DEFAULT NULL ,
        `store_id` SMALLINT(5) UNSIGNED NOT NULL,
        `message` TEXT DEFAULT NULL,
        `status` SMALLINT(5) UNSIGNED NOT NULL,
      PRIMARY KEY  (`invitation_id`),
      KEY `FK_GIANTPOINTS_REFER_CUSTOMER_ID` (`referral_id`),
      CONSTRAINT `FK_GIANTPOINTS_REFER_CUSTOMER_ID` FOREIGN KEY (`referral_id`) REFERENCES {$this->getTable('customer/entity')} (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");
try { /*Add column to sales_order table*/
    $installer->getConnection()->addColumn($this->getTable('sales/order'), 'invitee_earn', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/order'), 'referral_earn', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/order'), 'referral_id', 'int(11) NOT NULL default 0');
    /*Add column to sales_order_item table*/
    $installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'invitee_earn', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'referral_earn', 'int(11) NOT NULL default 0');
    /*Add column to sales_invoice table*/
    $installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'invitee_earn', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'referral_earn', 'int(11) NOT NULL default 0');
    /* add column into sales_flat_invoice_item table */
    $installer->getConnection()->addColumn($this->getTable('sales/invoice_item'), 'invitee_earn', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/invoice_item'), 'referral_earn', 'int(11) NOT NULL default 0');
    /*Add column to sales_creditmemo table*/
    $installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'invitee_earn', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'referral_earn', 'int(11) NOT NULL default 0');
    /* add column into sales_flat_invoice_item table */
    $installer->getConnection()->addColumn($this->getTable('sales/creditmemo_item'), 'invitee_earn', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/creditmemo_item'), 'referral_earn', 'int(11) NOT NULL default 0');
} catch (Exception $e) {
}
$installer->endSetup();

