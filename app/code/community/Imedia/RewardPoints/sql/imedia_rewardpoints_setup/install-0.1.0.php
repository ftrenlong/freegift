<?php

$installer = $this;

$installer->startSetup();


$installer->run("

		-- DROP TABLE IF EXISTS {$this->getTable('rewardpoints_rewardpoints')};
		CREATE TABLE {$this->getTable('rewardpoints_rewardpoints')} (
		  `id` int(11) unsigned NOT NULL auto_increment, 
		  `points` int(11) NOT NULL,
		  `entity_id` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

		ALTER TABLE  `".$this->getTable('sales/quote_address')."` ADD  `fee_amount` DECIMAL( 10, 2 ) NOT NULL;
		ALTER TABLE  `".$this->getTable('sales/quote_address')."` ADD  `base_fee_amount` DECIMAL( 10, 2 ) NOT NULL;
		
		ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `fee_amount` DECIMAL( 10, 2 ) NOT NULL;
		ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `base_fee_amount` DECIMAL( 10, 2 ) NOT NULL;
		ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `fee_amount_invoiced` DECIMAL( 10, 2 ) NOT NULL;
		ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `base_fee_amount_invoiced` DECIMAL( 10, 2 ) NOT NULL;
		ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `fee_amount_refunded` DECIMAL( 10, 2 ) NOT NULL;
		ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `base_fee_amount_refunded` DECIMAL( 10, 2 ) NOT NULL;
		
		ALTER TABLE  `".$this->getTable('sales/invoice')."` ADD  `fee_amount` DECIMAL( 10, 2 ) NOT NULL;
		ALTER TABLE  `".$this->getTable('sales/invoice')."` ADD  `base_fee_amount` DECIMAL( 10, 2 ) NOT NULL;
		
		
		
		ALTER TABLE  `".$this->getTable('sales/creditmemo')."` ADD  `fee_amount` DECIMAL( 10, 2 ) NOT NULL;
		ALTER TABLE  `".$this->getTable('sales/creditmemo')."` ADD  `base_fee_amount` DECIMAL( 10, 2 ) NOT NULL;
		
	
		
		-- DROP TABLE IF EXISTS {$this->getTable('rewardpoints_account')};
		CREATE TABLE {$this->getTable('rewardpoints_account')} (
		  `rewardpoints_account_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `customer_id` int(11) unsigned NOT NULL DEFAULT '0',
		  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0',
		  `points_current` int(5) unsigned NOT NULL DEFAULT '0',
		  `points_received` int(5) unsigned NOT NULL DEFAULT '0',
		  `points_spent` int(5) unsigned NOT NULL DEFAULT '0',
		  PRIMARY KEY (`rewardpoints_account_id`),
		  KEY `FK_catalog_category_ENTITY_STORE` (`store_id`),
		  KEY `customer_idx` (`customer_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Reward points for an account' AUTO_INCREMENT=1 ;

    ");
	
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'reward_points', array(
    'group'             => 'General',
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Points',
    'input'             => 'text',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'default'           => 0,
    'user_defined'      => true,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => 0,   //0 For All Product Types
    'is_configurable'   => false,
));
	
	
	
$installer->endSetup();