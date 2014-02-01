<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

// Add reset password link token column
$installer->getConnection()->addColumn($installer->getTable('admin/user'), 'twofactorauth', array(
    'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0',
    'comment' => 'Use Google Authenticator Two Factor Authentication'
));

$installer->endSetup();
