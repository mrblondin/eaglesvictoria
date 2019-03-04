<?php
class ControllerExtensionTotalPartialPaymentTotal extends Controller {
	private $error = array();
    public function index() {
        
    // die();
        //$data['heading_title'] = $this->language->get('heading_title');
        
    }
    public function partialCron() {
        $this->load->language('extension/total/partial_payment_total');
        $this->load->model('setting/setting');
        $this->load->model('setting/store');
        $this->load->model('account/customer');
        $this->load->model('checkout/order');
          echo '<pre>'; print_r('Partial Payment Total Reminder...'); echo '</pre>';
		if($this->config->get('total_partial_payment_total_token') != $_GET['token']){
          echo '<pre>'; print_r('Invalid Token...'); echo '</pre>';
          die();
            }
    
        $results = $this->db->query("SELECT o.order_id, 
            CONCAT(o.firstname, ' ', o.lastname) AS customer, 
            (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int) $this->config->get('config_language_id') . "') AS order_status, 
            o.invoice_prefix, 
            o.invoice_no, 
            o.order_id,
            o.shipping_code, 
            o.total,
            o.currency_code, 
            o.currency_value, 
            o.date_added, 
            o.date_modified,
            o.pending_total,
            ot.value,
            pp.partial_percent,
            pp.date_next_pay,
            pp.partial_period,
            pp.date_reminder
            FROM `" . DB_PREFIX . "order` o
            LEFT JOIN `" . DB_PREFIX . "order_total` ot ON ot.order_id = o.order_id AND ot.code = 'total'
            LEFT JOIN `" . DB_PREFIX . "partial_payment` pp ON pp.order_id = o.order_id
            ");
        if ($results->num_rows) {
            $results = $results->rows;
        } else {
            $results = array();
        }
        
  
        foreach ($results as $result) {
            /**/
            $this->load->language('extension/total/partial_payment_total');
            if ($result['partial_period'] == 'week') {
                $text_partial_period = $this->language->get('text_partial_week');
            } elseif ($result['partial_period'] == 'month') {
                $text_partial_period = $this->language->get('text_partial_month');
            } elseif ($result['partial_period'] == 'year') {
                $text_partial_period = $this->language->get('text_partial_year');
            }
            
            $orders[] = array(
                'order_id' => $result['order_id'],
                'customer' => $result['customer'],
                'total' => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
                'pending_total' => $this->currency->format($result['pending_total'], $result['currency_code'], $result['currency_value']),
                'pending_total2' => $result['pending_total'],
                'partial_percent' => $result['partial_percent'],
                'partial_period' => $result['partial_period'],
                'date_next_pay2' => strtotime($result['date_next_pay']),
                'date_reminder' => strtotime($result['date_reminder']),
                'date_now' => time(),
                'date_next_pay' => date($this->language->get('date_format_short'), strtotime($result['date_next_pay']))
                );
            }
 
        $data['text_subject'] = $this->language->get('text_subject');
   
        $json = array();
 
        foreach ($orders as $order) {
                
 		if ($order['partial_period']) {
			$order['partial_period'] = $order['partial_period'];
		} elseif (!is_null($this->config->get('total_partial_payment_total_cron_period'))) {
			$order['partial_period'] = $this->config->get('total_partial_payment_total_cron_period');
		} else {
			$order['partial_period'] = '';
		}
            
            $date = new DateTime('now');
            $date->modify('+1' . $order['partial_period']);
            $date_next_reminder = $date->format('Y-m-d H:i:s');
   
            if ((($order['date_next_pay2'] < $order['date_now']) && ($order['pending_total2'] > 0)) || (($order['date_reminder'] < $order['date_now']) && ($order['pending_total2'] > 0))) {
             
                $order_id = $order['order_id'];
				
			echo '<pre> Order-ID:'; print_r($order['order_id']); echo '</pre>';
			echo '<pre> Date Next reminder:'; print_r(date($this->language->get('date_format_short'), strtotime($date_next_reminder))); echo '</pre>';
			echo '<pre> Date Next payment:'; print_r($order['date_next_pay']); echo '</pre>';
			echo '<pre>'; print_r('------------'); echo '</pre>';
                
                $this->db->query("UPDATE " . DB_PREFIX . "partial_payment SET date_reminder = STR_TO_DATE('" . $date_next_reminder . "', '%Y-%m-%d %H:%i:%s') WHERE  order_id = '" . (int) $order_id . "'");
                
     
                if (!$json) {
                    if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
                        $data['logo'] = $this->config->get('config_url') . 'image/' . $this->config->get('config_logo');
                    } else {
                        $data['logo'] = '';
                    }
           
                    //echo '<pre>logo:'; print_r($data['logo']); echo '</pre>';
            
                    $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int) $order_id . "'");
                    if ($query->num_rows) {
                        $email = $query->row['email'];
                    } else {
                        $email = '';
                    }
                    
                    if ($query->num_rows) {
                        $store_id = $query->row['store_id'];
                    } else {
                        $store_id = '';
                    }
                    if ($query->num_rows) {
                        $pending_total = $query->row['pending_total'];
                    } else {
                        $pending_total = '';
                    }
                    if ($query->num_rows) {
                        $firstname = $query->row['firstname'];
                    } else {
                        $firstname = '';
                    }
                    if ($query->num_rows) {
                        $currency_value = $query->row['currency_value'];
                    } else {
                        $currency_value = '';
                    }
                    if ($query->num_rows) {
                        $currency_code = $query->row['currency_code'];
                    } else {
                        $currency_code = '';
                    }
                    /*        
                    $store_info = $this->model_setting_store->getStore($store_id);
                    
                    if ($store_info) {
                    $store_name = $store_info['name'];
                    } else {
                    $store_name = $this->config->get('config_name');
                    }
                    */
                    $store_name = $this->config->get('config_name');
                    
                    $data['text_thank_you'] = sprintf($this->language->get('text_thank_you'), $store_name);
                    
                    
                    
                    $setting     = $this->model_setting_setting->getSetting('config', $store_id);
                    $store_email = isset($setting['config_email']) ? $setting['config_email'] : $this->config->get('config_email');
                    
                    
                    $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
                    $message .= '<html dir="ltr" lang="en">' . "\n";
                    $message .= '  <head>' . "\n";
                    $message .= '    <title> <h4>' . $this->config->get('partial_payment_total_subject') . '</h4></title>' . "\n";
                    $message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
                    $message .= '  </head>' . "\n";
                    $message .= '  <body>';
                    $message .= '<table width="70%" border="0" cellpadding="5">
                <tbody>
                 <tr>    
                        <th colspan="2"><img style="float: left;" src="' . $data['logo'] . '"></th>
                </tr>
                <tr>
                        <td style="padding-bottm: 10px" width="30%"><strong>' . html_entity_decode($firstname, ENT_QUOTES, 'UTF-8') . '</strong></td>
                        <td> </td>
                </tr>
                <tr>
                        <td width="30%">' . html_entity_decode($this->language->get('text_order_id'), ENT_QUOTES, 'UTF-8') . '</td>
                        <td> #' . $order_id . '</td>
                </tr>
                <tr>
                        <td width="30%">' . html_entity_decode($this->language->get('text_payment_pending'), ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . $this->currency->format($pending_total, $currency_code, $currency_value) . '</td>
                </tr>';
                    if ($order['partial_period']) {
                        $message .= '<tr>
                        <td width="30%">' . html_entity_decode($this->language->get('text_period_send'), ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . $text_partial_period . '</td> 
                </tr>';
                    }
                    $message .= '<tr>
                        <td width="30%">' . html_entity_decode($this->language->get('text_payment_request'), ENT_QUOTES, 'UTF-8') . '</td>
                        <td><a href="' . $this->url->link('account/order/info&order_id=' . $order_id) . '">' . $this->url->link('account/order/info&order_id=' . $order_id) . '</a></td>
                </tr>
            </tbody>
        </table>';
                    
                    $message .= '<br /><br />' . $data['text_thank_you'];
                    
                    $message .= '</body>' . "\n";
                    $message .= '</html>' . "\n";
                    
                    $mail                = new Mail();
                    $mail->protocol      = $this->config->get('config_mail_protocol');
                    $mail->parameter     = $this->config->get('config_mail_parameter');
                    $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                    $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                    $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                    $mail->smtp_port     = $this->config->get('config_mail_smtp_port');
                    $mail->smtp_timeout  = $this->config->get('config_mail_smtp_timeout');
                    
                    $mail->setTo($email);
                    $mail->setFrom($store_email);
                    $mail->setSender(html_entity_decode($store_name, ENT_QUOTES, 'UTF-8'));
                    $mail->setSubject(html_entity_decode($this->language->get('text_subject'), ENT_QUOTES, 'UTF-8'));
                    $mail->setHtml($message);
                    $mail->send();

                }
            }
        }
    }
}