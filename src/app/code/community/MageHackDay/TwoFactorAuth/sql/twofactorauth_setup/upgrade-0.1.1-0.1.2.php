<?php /** @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$this->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('twofactorauth/user_cookie')}` (
  `cookie_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Cookie ID',
  `user_id` int(10) unsigned NOT NULL COMMENT 'Admin User ID',
  `cookie` varchar(40) COMMENT 'Cookie Value',
  PRIMARY KEY (`cookie_id`),
  UNIQUE KEY `UNQ_ADMIN_USER_COOKIE_USER_ID_COOKIE` (`user_id`, `cookie`),
  CONSTRAINT `FK_ADMIN_USER_COOKIE_USER_ID` FOREIGN KEY (`user_id`)
    REFERENCES {$this->getTable('admin/user')} (`user_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;
");

$this->endSetup();
