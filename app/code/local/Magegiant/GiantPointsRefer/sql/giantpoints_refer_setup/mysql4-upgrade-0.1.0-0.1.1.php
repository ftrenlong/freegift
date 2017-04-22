<?php

$installer = $this;

$installer->startSetup();

/*ShoppingCart Rule Begin*/
$installer->getConnection()->dropTable($installer->getTable('giantpointsrefer/salesrule'));
$table = $installer->getConnection()
    ->newTable($installer->getTable('giantpointsrefer/salesrule'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Rule Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(), 'Description')
    ->addColumn('from_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(), 'From Date')
    ->addColumn('to_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(), 'To Date')
    ->addColumn('customer_group_ids', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(), 'Customer Group Ids')
    ->addColumn('referrals_group_ids', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(), 'Referral\'s  Group Ids')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '0',
    ), 'Is Active')
    ->addColumn('conditions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(), 'Conditions Serialized')
    ->addColumn('actions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(), 'Actions Serialized')
    ->addColumn('stop_rules_processing', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '1',
    ), 'Stop Rules Processing')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => '0',
    ), 'Sort Order')
    ->addColumn('simple_action', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable' => false,
        'default'  => 'fixed',
    ), 'Simple Action')
    ->addColumn('point_amount', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'unsigned' => true,
        'nullable' => false,
    ), 'Point Amount')
    ->addColumn('money_step', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12, 4), array(
        'unsigned' => true,
        'nullable' => false,
    ), 'Money step')
    ->addColumn('referrals_simple_action', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable' => false,
        'default'  => 'fixed',
    ), 'Referral\'s action type')
    ->addColumn('referrals_point_amount', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'unsigned' => true,
        'nullable' => false,
    ), 'Referral\'s points amount')
    ->addColumn('website_ids', Varien_Db_Ddl_Table::TYPE_TEXT, 4000, array(), 'Website Ids')
    ->addIndex($installer->getIdxName('giantpointsrefer/salesrule', array('is_active', 'sort_order', 'to_date', 'from_date')),
        array('is_active', 'sort_order', 'to_date', 'from_date'))
    ->setComment('GiantPoints Refer Friends Sales Rule');
$installer->getConnection()->createTable($table);

$installer->endSetup();