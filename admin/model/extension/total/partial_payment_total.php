<?php
/* Partial Payment Total for OpenCart v.3.0.x 
 *
 * @version 3.3.1
 * @date 05/09/2018
 * @author Kestutis Banisauskas
 * @Smartechas
 */
class ModelExtensionTotalPartialPaymentTotal extends Model {
    
 public function install(){  
 
  $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "partial_payment` (
			  `partial_payment_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `order_id` INT(11) NOT NULL,
			  `partial_period` VARCHAR(50) NOT NULL,
			  `partial_percent` INT(11) NOT NULL,
			  `pending_total`  DECIMAL( 15, 4 ) NOT NULL,
			  `partial_amount`  DECIMAL( 15, 4 ) NOT NULL,
			  `total` DECIMAL( 15, 4 ) NOT NULL,
			  `date_added` DATETIME NOT NULL,
			  `date_reminder` DATETIME NOT NULL,
			  `date_next_pay` DATETIME NOT NULL,
			   PRIMARY KEY (`partial_payment_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

 $query = $this->db->query("DESC `".DB_PREFIX."partial_payment` date_reminder");
if (!$query->num_rows) {
   $this->db->query("ALTER TABLE `" . DB_PREFIX . "partial_payment` ADD `date_reminder` DATETIME NOT NULL");
		}
 
 $query = $this->db->query("DESC `".DB_PREFIX."order` pending_total");
if (!$query->num_rows) {
   $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `pending_total`  DECIMAL( 15, 4 ) NOT NULL");
		}
	} 
}





