<?php
class ControllerExtensionVerifyemail extends Controller {
	private $error = array();
	private $modname = 'custverf';
 	private $modtpl = 'default/template/extension/verifyemail.tpl'; 
	private $modpath = 'extension/verifyemail'; 
	private $modssl = 'SSL';
	private $modlangid = '';
	private $modstoreid = '';
	private $modgrpid = '';	
	
	public function __construct($registry) {
		parent::__construct($registry);
		
		if(substr(VERSION,0,3)>='3.0') { 
			$this->modname = 'module_custverf';
		} 
 		
		if(substr(VERSION,0,3)>='3.0' || substr(VERSION,0,3)=='2.3' || substr(VERSION,0,3)=='2.2') { 
			$this->modssl = true;
			$this->modtpl = 'extension/verifyemail'; 
		} 
		
		$this->modlangid = (int)$this->config->get('config_language_id');
		$this->modgrpid = (int)$this->config->get('config_customer_group_id');
		$this->modstoreid = (int)$this->config->get('config_store_id');
 	} 
	
	protected function setvalue($postfield) {
		return $this->config->get($postfield);
	} 

	public function index() {
 		$this->load->language('account/account');
		$this->load->language($this->modpath);		

		$this->document->setTitle($this->language->get('heading_title'));

 		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', $this->modssl)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->modpath, '', $this->modssl)
		);

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['homelink'] = $this->url->link('account/login');
 		
		if(isset($this->request->get['token']))	{
			$custverf_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "custverf WHERE token = '" . $this->db->escape($this->request->get['token']) . "' and confirmed = 0");
			if($custverf_query->row) {
 				$this->db->query("UPDATE " . DB_PREFIX . "custverf set confirmed=1 WHERE token='" . $this->db->escape($this->request->get['token']) . "' and customer_id='" . (int)$custverf_query->row['customer_id'] . "' ");
				
				$data['custverf_approve_verified'] = $this->setvalue($this->modname.'_approve_verified');
				
				if($data['custverf_approve_verified']) {
					if(substr(VERSION,0,3)>='3.0') { 
						$this->db->query("UPDATE " . DB_PREFIX . "customer set status=1 WHERE customer_id='" . (int)$custverf_query->row['customer_id'] . "' ");
						$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_approval` WHERE customer_id='" . (int)$custverf_query->row['customer_id'] . "' AND `type` = 'customer'");
					} else {
						$this->db->query("UPDATE " . DB_PREFIX . "customer set approved=1 WHERE customer_id='" . (int)$custverf_query->row['customer_id'] . "' ");
					}
				}
 				
				$data['custverf_admin_verf_subject'] = $this->setvalue($this->modname.'_admin_verf_subject');
				$data['custverf_admin_verf_subject'] = $data['custverf_admin_verf_subject'][$this->modlangid];
				
				$data['custverf_admin_verf_template'] = $this->setvalue($this->modname.'_admin_verf_template');
				$data['custverf_admin_verf_template'] = html_entity_decode($data['custverf_admin_verf_template'][$this->modlangid], ENT_QUOTES, 'UTF-8');
				
				// get customer details //
				$customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$custverf_query->row['customer_id'] . "'");
				$customer_query->row['email'];
				
				$data['custverf_admin_verf_template'] .= '<div>
 				<p>'.$this->language->get('txt_customer_id').' : '.$customer_query->row['customer_id'].'</p>
				<p>'.$this->language->get('txt_fname').' : '.$customer_query->row['firstname'].'</p>
				<p>'.$this->language->get('txt_lname').' : '.$customer_query->row['lastname'].'</p>
 				<p>'.$this->language->get('txt_email').' : '.$customer_query->row['email'].'</p>
				<p>'.$this->language->get('txt_telephone').' : '.$customer_query->row['telephone'].'</p></div>';
				
				// shoot admin email
				
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
	
				$mail->setTo($this->config->get('config_email'));
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
				$mail->setSubject($data['custverf_admin_verf_subject']);
				$mail->setHtml($data['custverf_admin_verf_template']);
				$mail->send();
	
				// Send to additional alert emails if new account email is enabled
				if($admin_emails) {
					foreach ($admin_emails as $email) {
						if (utf8_strlen($email) > 0 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
							$mail->setTo($email);
							$mail->send();
						}
					}
				}
				
				// print email verifiy done thank you page //
				$data['custverf_thankyou_verf_template'] = $this->setvalue($this->modname.'_thankyou_verf_template');
				$data['custverf_thankyou_verf_template'] = html_entity_decode($data['custverf_thankyou_verf_template'][$this->modlangid], ENT_QUOTES, 'UTF-8');
  			} else {
				$this->response->redirect($this->url->link('common/home', '', true)); 
			}
		} else {
			$this->response->redirect($this->url->link('common/home', '', true)); 
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view($this->modtpl, $data));
	}
}