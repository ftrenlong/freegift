<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create atttibute free gift for product
 */

$installer->addAttribute(catalog_product, 'free_gift_product', array(
    'group'         => 'General',
    'input'         => 'select',
    'type'          => 'text',
    'label'         => 'Free gift product',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'source' => 'eav/entity_attribute_source_boolean',
    'sort_order'        => 9999,
));	
$installer->addAttribute(catalog_product, 'points', array(
    'group'         => 'General',
    'input'         => 'text',
    'type'          => 'int',
    'label'         => 'Reward points',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
//    'source' => 'eav/entity_attribute_source_boolean',
    'sort_order'        => 10000,
));


$installer->getConnection()
->addColumn($installer->getTable('salesrule/rule'),'free_gift_apply', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'nullable'  => false,
    'length'    => 5,
    'after'     => null, // column name to insert new column after
    'comment'   => 'this attribute of salesrule table '
    ));

$installer->getConnection()
->addColumn($installer->getTable('catalogrule/rule'),'free_gift_apply', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'nullable'  => false,
    'length'    => 5,
    'after'     => null, // column name to insert new column after
    'comment'   => 'this attribute of catalogrule table '
    ));


$installer->endSetup();

