<?php
$installer = $this;
$installer->startSetup();

if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
    $setup = new Mage_Sales_Model_Resource_Setup('core_setup');
} else {
    $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
}

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/quote'), 'pickup_pointofsale_id', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned'  => true,
            'nullable'  => true,
            'default'   => null,
            'comment'   => 'Pickup point of sale ID'
        )
    );

$installer->getConnection()
    ->addIndex(
        $installer->getTable('sales/quote'), 
        'IDX_SALES_FLAT_QUOTE_PICKUP_POINTOFSALE_ID', 
        'pickup_pointofsale_id'
    );

$setup->addAttribute('quote', 'pickup_pointofsale_id', array('type' => 'static', 'visible' => false));

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/order'), 'pickup_pointofsale_id', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned'  => true,
            'nullable'  => true,
            'default'   => null,
            'comment'   => 'Pickup point of sale ID'
        )
    );

$installer->getConnection()
    ->addIndex(
        $installer->getTable('sales/order'), 
        'IDX_SALES_FLAT_ORDER_PICKUP_POINTOFSALE_ID', 
        'pickup_pointofsale_id'
    );

$setup->addAttribute('order', 'pickup_pointofsale_id', array('type' => 'static', 'visible' => false));

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/order_grid'), 'pickup_pointofsale_id', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned'  => true,
            'nullable'  => true,
            'default'   => null,
            'comment'   => 'Pickup point of sale ID'
        )
    );

$installer->getConnection()
    ->addIndex(
        $installer->getTable('sales/order_grid'), 
        'IDX_SALES_FLAT_ORDER_GRID_PICKUP_POINTOFSALE_ID', 
        'pickup_pointofsale_id'
    );

$setup->addAttribute('order', 'pickup_pointofsale_id', array('type' => 'static', 'visible' => false));

$installer->endSetup();
