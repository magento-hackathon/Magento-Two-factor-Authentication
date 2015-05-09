<?php /** @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$this->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('twofactorauth/user_question')}` (
  `question_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Question ID',
  `user_id` int(10) unsigned NOT NULL COMMENT 'Admin User ID',
  `question` varchar(100) NOT NULL COMMENT 'Question',
  `answer` varchar(255) NOT NULL COMMENT 'Answer Hash',
  PRIMARY KEY (`question_id`),
  UNIQUE KEY `UNQ_ADMIN_USER_QUESTION_USER_ID_QUESTION` (`user_id`, `question`),
  CONSTRAINT `FK_ADMIN_USER_QUESTION_USER_ID` FOREIGN KEY (`user_id`)
    REFERENCES {$this->getTable('admin/user')} (`user_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;
");

$this->endSetup();
