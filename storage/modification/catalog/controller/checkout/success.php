<?php
class ControllerCheckoutSuccess extends Controller {
	public function index() {
		$this->load->language('checkout/success');

		if (isset($this->session->data['order_id'])) {

            /*calculate store credit*/
              if ($this->customer->isLogged()) {
        
        $this->load->language('extension/total/credit');
        $this->load->language('extension/total/partial_payment_total');
		$this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        
        $query = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "order_total` WHERE `order_id` = " . (int)$order_info['order_id'] . " AND `code` = 'total'");

            if ($query->num_rows) {
                $data['total'] = $query->row['value'];
            } else {
                $data['total'] = '0';
            }
        
        $data['order_id'] = $order_info['order_id'];
        //////
		
		if (isset($this->session->data['total_partial_payment_total_percent_dd'])) {
			$data['total_partial_payment_total_percent_dd'] = $this->session->data['total_partial_payment_total_percent_dd'];
		} else {
			$data['total_partial_payment_total_percent_dd'] = '0';
		}
		
        $percents = explode(',', $this->config->get('total_partial_payment_total_percent'));
                
                //$status_percent = '';
                $total = $data['total'];

if (!($this->config->get('total_partial_payment_total_dd_select'))) {
                foreach ($percents as $percent) {
                    $data = explode(':', $percent);
                    
                    if ($data[0] >= $total) {
                        if (isset($data[1])) {
                            $data['total_partial_payment_total_percent'] = $data[1];
                            $status_percent = true;
                        } else {

                            $status_percent = false;
                        }

                        break;
						}
					} 
				} else {
						$data['total_partial_payment_total_percent'] = $data['total_partial_payment_total_percent_dd'];
				}
        /////        
    	$data['total_partial_payment_total_percent'] = isset($data['total_partial_payment_total_percent']) ? $data['total_partial_payment_total_percent'] : '0';
        $data['partial_amount'] = (int)$total*(int)$data['total_partial_payment_total_percent']/100;
        $data['total_partial_payment_total_total'] = $this->config->get('total_partial_payment_total_total');
        
        if (isset($this->session->data['partial_payment_total'])) {
            $data['partial_payment_total'] = $this->session->data['partial_payment_total'];
        } else {
            $data['partial_payment_total'] = '';
        }
		
			/* period/frequency for partial payment */
		if (isset($this->session->data['total_partial_payment_total_period'])) {
            $data['total_partial_payment_total_period'] = $this->session->data['total_partial_payment_total_period'];
        } elseif (!is_null($this->config->get('total_partial_payment_total_cron_period'))){
            $data['total_partial_payment_total_period'] = $this->config->get('total_partial_payment_total_cron_period');
        } else {
		 	$data['total_partial_payment_total_period'] = '';
		}
		 
        
        $pending_order_id = isset($this->session->data['pending_order_id']) ? $this->session->data['pending_order_id'] : '';
        $pending_amount = isset($this->session->data['pending_amount']) ? $this->session->data['pending_amount'] : '';
		$pending_amount_next = isset($this->session->data['pending_amount_next']) ? $this->session->data['pending_amount_next'] : '';
		
		$dates_next_pay = $this->db->query("SELECT * FROM `" . DB_PREFIX . "partial_payment` WHERE order_id = '" . (int)$pending_order_id . "'");
            if ($dates_next_pay->num_rows) {
                $date_next_pay = $dates_next_pay->row['date_next_pay'];
            } else {
                $date_next_pay = '';                
            }
			if ($dates_next_pay->num_rows) {
                $partial_period = $dates_next_pay->row['partial_period'];
            } else {
                $partial_period = '';                
            }
		
		if ($partial_period && $pending_amount_next) {
		$date = new DateTime($date_next_pay);
		$date->modify('+1'.  $partial_period);
		$date_next_pay = $date->format('Y-m-d H:i:s');
		} elseif ($data['total_partial_payment_total_period']) {
		$date = new DateTime('now');
		$date->modify('+1'.  $data['total_partial_payment_total_period']);
		$date_next_pay = $date->format('Y-m-d H:i:s');
		} else {
		$date = new DateTime('now');
		$date->modify('+1'. 'week'); // set period time (day, week, month, year) for next payment if customer not select period and in admin period not be period preselcted
		$date_next_pay = $date->format('Y-m-d H:i:s');
		}
		
        $totals = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$pending_order_id . "' AND code = 'total'");
            if ($totals->num_rows) {
                $total_value = $totals->row['value'];
            } else {
                $total_value = '';                
            }
            $partial_totals = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$pending_order_id . "' AND code = 'partial_payment_total'");
            if ($partial_totals->num_rows) {
                $partial_total = $partial_totals->row['value'];
            } else {
                $partial_total = '';                
            }
			
			
            if ($data['partial_payment_total'] || $data['total_partial_payment_total_percent_dd'] != 0) {
        $order_total['value'] = $data['partial_amount'] - $total;
        } elseif ($pending_amount_next != 0) {
        $order_total['value'] = $partial_total - $total + $pending_amount_next;
        } else {
		$order_total['value'] = '';
		}
		
		
            if ($data['partial_payment_total'] || $data['total_partial_payment_total_percent_dd'] != 0) {
        		$pending_total  = $total - $data['partial_amount'];
            } elseif ($pending_amount_next != 0) {
				$pending_total  = $total - $partial_total - $pending_amount_next;
			} else {
				$pending_total  = '';
			}
        
        
        $pending_customer_ids = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_transaction` WHERE order_id = '" . (int)$pending_order_id . "'");
            if ($pending_customer_ids->num_rows) {
                $pending_customer_id = $pending_customer_ids->row['customer_id'];
            } else {
                $pending_customer_id = '';                
            }
        
		$pending_total_topay = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$pending_order_id . "'");
            if ($pending_total_topay->num_rows) {
                $pending_total_pay = $pending_total_topay->row['pending_total'];
            } else {
                $pending_total_pay = '';                
            }
		 if (isset($this->session->data['vouchers'])) {
            $data['vouchers'] = $this->session->data['vouchers'];
        }else{
            $data['vouchers'] = array();
        }
		
		if (!$data['vouchers'] && $pending_amount != 0) {
			$pending_total_paid  = $pending_total_pay - $pending_amount;
        } elseif ($pending_amount_next != 0) {
			$pending_total_paid  =  $pending_total_pay - $pending_amount_next;
		} else {
			$pending_total_paid  =  '';
		}
	
		if ($pending_amount) {
			$pending_amount = $pending_amount;
		} elseif ($pending_amount_next) {
			$pending_amount = $pending_amount_next;
		}
		
           
		   
        if (($pending_amount == 0) && (($data['partial_payment_total']) || ($data['total_partial_payment_total_percent_dd'] != 0))) {
		
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "partial_payment SET order_id = '" . (int)$order_info['order_id'] . "', pending_total = '" . (float)$pending_total . "', partial_period = '" . $data['total_partial_payment_total_period'] . "', partial_percent = '" . (int)$data['total_partial_payment_total_percent'] . "', partial_amount = '" . (float)$data['partial_amount'] . "', total = '" . (float)$total . "', date_next_pay = STR_TO_DATE('" . $date_next_pay . "', '%Y-%m-%d %H:%i:%s'), date_reminder = STR_TO_DATE('" . $date_next_pay . "', '%Y-%m-%d %H:%i:%s'), date_added = NOW()");
		
		
        
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_transaction SET customer_id = '" . (int)$order_info['customer_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', description = '" . $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$order_info['order_id'])) . "', amount = '" . (float)$order_total['value'] . "', date_added = NOW()");
		
        $this->db->query("UPDATE " . DB_PREFIX . "order SET   total = '" . (float)$total . "', pending_total = '" . (float)$pending_total . "', date_added = NOW() WHERE  order_id = '" . (int)$order_info['order_id'] . "'");
        
            } elseif  ($pending_amount != 0 || $pending_amount_next != 0) {
			$this->db->query("UPDATE " . DB_PREFIX . "partial_payment SET pending_total = '" . (float)$pending_total_paid . "', date_next_pay = STR_TO_DATE('" . $date_next_pay . "', '%Y-%m-%d %H:%i:%s'), date_reminder = STR_TO_DATE('" . $date_next_pay . "', '%Y-%m-%d %H:%i:%s') WHERE  order_id = '" . (int)$pending_order_id . "'");
			
			
            $this->db->query("INSERT INTO " . DB_PREFIX . "customer_transaction SET customer_id = '" . (int)$pending_customer_id . "', order_id = '" . (int)$order_info['order_id'] . "', description = '" . $this->db->escape(sprintf($this->language->get('text_for'), (int)$pending_order_id)) . "', amount = '" . (float)$pending_amount . "', date_added = NOW()");
            
            $this->db->query("UPDATE " . DB_PREFIX . "order SET   total = '" . (float)$total_value . "', pending_total = '" . (float)$pending_total_paid . "', date_added = NOW() WHERE  order_id = '" . (int)$pending_order_id . "'");
            }
            
            unset($this->session->data['pending_order_id']);
            unset($this->session->data['pending_amount']);
            unset($this->session->data['partial_payment_total']);
			unset($this->session->data['total_partial_payment_total_percent_dd']);
			unset($this->session->data['total_partial_payment_total_period']);
			unset($this->session->data['pending_amount_next']);
     
    }
        /////////////////////////////////////////////////////////////////////
        
			$this->cart->clear();

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);

            unset($this->session->data['partial_payment_total']);
			unset($this->session->data['total_partial_payment_total_percent_dd']);
			unset($this->session->data['total_partial_payment_total_period']);
			unset($this->session->data['pending_amount_next']);
        
        
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_basket'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_checkout'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('checkout/success')
		);

		if ($this->customer->isLogged()) {
			$data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', true), $this->url->link('account/order', '', true), $this->url->link('account/download', '', true), $this->url->link('information/contact'));
		} else {
			$data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
		}

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success', $data));
	}
}