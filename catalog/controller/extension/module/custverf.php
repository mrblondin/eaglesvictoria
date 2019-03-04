<?php
class ControllerextensionModulecustverf extends Controller {
	private $error = array();
	private $modpath = 'module/custverf'; 
	private $modtpl = 'default/template/module/custverf.tpl'; 
	private $modthankyoutpl = 'default/template/module/verifythankyou.tpl'; 	
 	private $modname = 'custverf';
 	private $modtext = 'Customer Email Verification';
	private $modid = '31650';
	private $modssl = 'SSL';
	private $modemail = 'opencarttools@gmail.com';
	private $modlangid = '';
	private $modstoreid = '';
	private $modgrpid = '';	

	public function __construct($registry) {
		parent::__construct($registry);
 		
		if(substr(VERSION,0,3)>='3.0' || substr(VERSION,0,3)=='2.3') { 
			$this->modtpl = 'extension/module/custverf';
			$this->modthankyoutpl = 'extension/module/verifythankyou';			
   			$this->modpath = 'extension/module/custverf';
 		} else if(substr(VERSION,0,3)=='2.2') {
			$this->modtpl = 'module/custverf';
			$this->modthankyoutpl = 'module/verifythankyou';
  		} 
		
		if(substr(VERSION,0,3)>='3.0') { 
			$this->modname = 'module_custverf';
		} 
		
		if(substr(VERSION,0,3)>='3.0' || substr(VERSION,0,3)=='2.3' || substr(VERSION,0,3)=='2.2') { 
			$this->modssl = true;
		} 
		
		$this->modlangid = (int)$this->config->get('config_language_id');
		$this->modgrpid = (int)$this->config->get('config_customer_group_id');
		$this->modstoreid = (int)$this->config->get('config_store_id');
 	}
	
	public function index($customer_id) {	
		if($this->setvalue($this->modname.'_status')) {
		
		$data['custverf_verf_subject'] = $this->setvalue($this->modname.'_verf_subject');
		$data['custverf_verf_subject'] = $data['custverf_verf_subject'][$this->modlangid];
 		
		$data['custverf_verf_template'] = $this->setvalue($this->modname.'_verf_template');
		$data['custverf_verf_template'] = html_entity_decode($data['custverf_verf_template'][$this->modlangid], ENT_QUOTES, 'UTF-8');

        // get customer email
        $customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");
        $customer_query->row['email'];
		
		//$custverf_token = substr(md5(mt_rand()), 0, 20);
        $custverf_token = substr(md5($customer_query->row['email']), 0, 20);
		$this->db->query("INSERT INTO " . DB_PREFIX . "custverf set token='" . $this->db->escape($custverf_token) . "', customer_id='" . (int)$customer_id . "', confirmed=1 "); // confirmed=0 if we want verification
		//$confirm_link = $this->url->link('extension/verifyemail', 'token=' . $custverf_token);
		
		//$data['custverf_verf_template'] .= '<div><p><a href="'.$confirm_link.'">'.$confirm_link.'</a></p></div>';

		
		// shoot email to customer
		if(substr(VERSION,0,3)>='3.0') {
 			$mail = new Mail($this->config->get('config_mail_engine'));
			$admin_emails = explode(',', $this->config->get('config_mail_alert_email'));
 		} else {
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			if(substr(VERSION,0,3)>='2.3') {
				$admin_emails = explode(',', $this->config->get('config_mail_alert_email'));
			} else {
				$admin_emails = explode(',', $this->config->get('config_mail_alert'));
			}
  		}
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($customer_query->row['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject($data['custverf_verf_subject']);
		$mail->setHtml($data['custverf_verf_template']);
 		//$mail->send();
		}
	} 
	
	protected function setvalue($postfield) {
		return $this->config->get($postfield);
	} 
}