<?php

/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// Add our store ID's next to the address fields
$installer->addAttribute(
    'quote_address', 'doddle_store_id', array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR, 'visible' => false)
);
$installer->addAttribute(
    'order_address', 'doddle_store_id', array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR, 'visible' => false)
);

// Create our API requests table
$requestTable = $installer->getConnection()->newTable($installer->getTable('gene_doddle/request'))
    ->addColumn('request_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 9, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'identity' => true,
        'auto_increment' => true
    ), 'Request ID')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'nullable' => false
    ), 'Order ID')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        'nullable' => true
    ), 'Status of request')
    ->addColumn('message', Varien_Db_Ddl_Table::TYPE_TEXT, false, array(
        'nullable' => true
    ), 'Any message associated with the failure of the request')
    ->addColumn('preadvice_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => true
    ), 'Video to play from header area')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, false, array(
        'nullable' => true
    ), 'Created at time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, false, array(
        'nullable' => true
    ), 'Updated at time')
    ->setComment('Doddle API Requests Table');

// Actually create the table
$installer->getConnection()->createTable($requestTable);

$installer->endSetup();