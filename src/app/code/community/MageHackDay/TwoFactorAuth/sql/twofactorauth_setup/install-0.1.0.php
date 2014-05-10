<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

// Add reset password link token column
$installer->getConnection()->addColumn($installer->getTable('admin/user'), 'twofactor_token', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'comment' => 'Google Authenticator Token'
));

$installer->endSetup();
