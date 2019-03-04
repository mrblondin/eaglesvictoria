<?php
class ControllerAccountOrder extends Controller {

            
            public function payBalance() {                     
            
            $this->load->language('extension/total/partial_payment_total');    
            $this->load->language('account/order');

            
            $this->load->language('extension/total/partial_payment_total');
            
            $data['column_pending'] = $this->language->get('column_pending');
			$data['column_next_pay'] = $this->language->get('column_next_pay');

            
    
            $json = array();                    
            if (isset($this->request->post['order_id'])) {        
            $order_id = $this->request->post['order_id'];        
            } else {    
            $order_id = '';        
			}  
			
			if (isset($this->request->post['amount_next'])) {        
            $amount_next = $this->request->post['amount_next'];        
            } else {    
            $amount_next = '';        
			}    
			
			if (isset($this->request->post['amount'])) {        
            $amount = $this->request->post['amount'];        
            } else {    
            $amount = '';        
			} 
			
			if ($amount) {
			$pending_payment = $amount;
			} elseif ($amount_next) {
			$pending_payment= $amount_next;
			} else {
			$pending_payment = '';
			}
			

            $this->load->model('account/order');        
    
            
            $order_info = $this->model_account_order->getOrder($order_id);    

            if ($order_info) {            
            
            if(!$json){                                    
            if (!isset($this->session->data['vouchers'])) {    
            $this->session->data['vouchers']
            = array();            
            }                    

            if (isset($this->session->data['pending_total'])) {
            unset($this->session->data['pending_total']);    
            unset($this->session->data['pending_amount']);    
            unset($this->session->data['amount']);
            unset($this->session->data['partial_payment_total']);    
            }            
            
            if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
                $balance_order_id = $this->request->post['order_id'];
                unset($this->session->data['vouchers']);
                $this->session->data['vouchers'][rand()] = array(            
                    'description'      => sprintf($this->language->get('text_for'), $balance_order_id),            
                    'to_name'          => 'pending_total',            
                    'to_email'         => 'purchased',            
                    'from_name'        => $this->customer->getId(),        
                    'from_email'       => '',            
                    'message'          => sprintf($this->language->get('text_for'), $balance_order_id),        
                    //'amount'           => $this->request->post['amount'],
					'amount'           => $pending_payment,     
                    'voucher_theme_id' => 0    
                );    
                $this->session->data['pending_order_id'] = $this->request->post['order_id'];
                //$this->session->data['pending_amount'] = $this->currency->convert($this->request->post['amount'], $this->session->data['currency'], $this->config->get('config_currency'));
                           // $this->session->data['pending_amount'] = $this->request->post['amount'];
							$this->session->data['pending_amount'] = $amount;
							$this->session->data['pending_amount_next'] = $amount_next;
                
                    
                        
                $json['redirect'] = str_replace('&amp;', '&', $this->url->link('checkout/checkout'));    
                    
            }            
        }            
    }            
            
            $this->response->setOutput(json_encode($json));        
            
            }
            
           
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/order');

            
            $this->load->language('extension/total/partial_payment_total');
            
            $data['column_pending'] = $this->language->get('column_pending');
			$data['column_next_pay'] = $this->language->get('column_next_pay');

            

		$this->document->setTitle($this->language->get('heading_title'));
		
		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/order', $url, true)
		);

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['orders'] = array();

		$this->load->model('account/order');

		$order_total = $this->model_account_order->getTotalOrders();

		$results = $this->model_account_order->getOrders(($page - 1) * 10, 10);

		foreach ($results as $result) {
			$product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
			$voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);

			$data['orders'][] = array(
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'] . ' ' . $result['lastname'],
				'status'     => $result['status'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'products'   => ($product_total + $voucher_total),
				'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),

            //'total'         => $this->currency->format($result['value'], $result['currency_code'], $result['currency_value']),
                'pending_total'    => $this->currency->format($result['pending_total'], $result['currency_code'], $result['currency_value']),
				'pending_total2'    => $result['pending_total'],
				'date_next_pay'    => date($this->language->get('date_format_short'), strtotime($result['date_next_pay'])),
				'date_next_pay2'    => strtotime($result['date_next_pay']),
				//'date_now'    => date($this->language->get('date_format_short'), strtotime((new DateTime('now'))->format('Y-m-d H:i:s'))),
				'date_now'    => time(),
				'partial_period'    => $result['partial_period'], 
            
				'view'       => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], true),
			);
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('account/order', 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($order_total - 10)) ? $order_total : ((($page - 1) * 10) + 10), $order_total, ceil($order_total / 10));

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/order_list', $data));
	}

	public function info() {
		$this->load->language('account/order');

            
            $this->load->language('extension/total/partial_payment_total');
            
            $data['column_pending'] = $this->language->get('column_pending');
			$data['column_next_pay'] = $this->language->get('column_next_pay');

            

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info) {
			$this->document->setTitle($this->language->get('text_order'));

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('account/order', $url, true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_order'),
				'href' => $this->url->link('account/order/info', 'order_id=' . $this->request->get['order_id'] . $url, true)
			);

			if (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			if ($order_info['invoice_no']) {
				$data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$data['invoice_no'] = '';
			}

			$data['order_id'] = $this->request->get['order_id'];
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			);

			$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$data['payment_method'] = $order_info['payment_method'];

			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'company'   => $order_info['shipping_company'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			);

			$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$data['shipping_method'] = $order_info['shipping_method'];

			$this->load->model('catalog/product');
			$this->load->model('tool/upload');

			// Products
			$data['products'] = array();

			$products = $this->model_account_order->getOrderProducts($this->request->get['order_id']);

			foreach ($products as $product) {
				$option_data = array();

				$options = $this->model_account_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

				foreach ($options as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$product_info = $this->model_catalog_product->getProduct($product['product_id']);

				if ($product_info) {
					$reorder = $this->url->link('account/order/reorder', 'order_id=' . $order_id . '&order_product_id=' . $product['order_product_id'], true);
				} else {
					$reorder = '';
				}

				$data['products'][] = array(
					'name'     => $product['name'],
					'model'    => $product['model'],
					'option'   => $option_data,
					'quantity' => $product['quantity'],
					'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'reorder'  => $reorder,
					'return'   => $this->url->link('account/return/add', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], true)
				);
			}

			// Voucher
			$data['vouchers'] = array();

			$vouchers = $this->model_account_order->getOrderVouchers($this->request->get['order_id']);

			foreach ($vouchers as $voucher) {
				$data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}

			// Totals
			$data['totals'] = array();


            $this->load->language('extension/total/partial_payment_total');
            $data['button_balance_pay'] = $this->language->get('button_balance_pay');
			$data['button_balance_pay_next'] = $this->language->get('button_balance_pay_next');
			$data['entry_balance_pay_next'] = $this->language->get('entry_balance_pay_next');
            $data['entry_balance_pay'] = $this->language->get('entry_balance_pay');
            $data['entry_pending'] = $this->language->get('entry_pending');
            
            //$pending_totals = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");
			
			$pending_totals = $this->db->query("SELECT 
			o.pending_total,
			o.total,
			pp.partial_period,
			pp.partial_amount,
			pp.partial_percent,
			pp.date_next_pay
			FROM `" . DB_PREFIX . "order` o
			LEFT JOIN `" . DB_PREFIX . "partial_payment` pp ON pp.order_id = o.order_id WHERE o.order_id = '" . (int)$order_id . "'");
			
		////////////////////////////////////////////////////////////////////////////////////////////////////	
			
			
            if ($pending_totals->num_rows) {
                $pending_total = $pending_totals->row['pending_total'];
            } else {
                $pending_total = '';                
            }
            if ($pending_totals->num_rows) {
                $total = $pending_totals->row['total'];
            } else {
                $total = '';                
            }
			if ($pending_totals->num_rows) {
                $partial_period = $pending_totals->row['partial_period'];
            } else {
                $partial_period = '';                
            }
			
			if ($pending_totals->num_rows) {
                $partial_amount = $pending_totals->row['partial_amount'];
            } else {
                $partial_amount = '';                
            }
			
			if ($pending_totals->num_rows) {
                $partial_percent = $pending_totals->row['partial_percent'];
            } else {
                $partial_percent = '';                
            }
			
			if ($partial_percent < 50 && $partial_period) {
			$pending_total_next = $partial_amount;
			} elseif ($partial_percent < 50 &&  $partial_amount < $pending_total){
			$pending_total_next = $pending_total - $partial_amount;
			} else {
			$pending_total_next = '';
			}
			///////////////////////////////////////////////////////////////////////////////////////////////////////
			
			$data['pending'] = $pending_total;
            $data['pending_total'] = $this->currency->format($pending_total, $order_info['currency_code'], $order_info['currency_value']);
			$data['pending_total_next'] = $this->currency->format($pending_total_next, $order_info['currency_code'], $order_info['currency_value']);
            
             
            $data['pendings'][] = array(
            'total' => $total, 
            'pending_total' => $pending_total,
			'partial_amount' => $partial_amount,
			'partial_period' => $partial_period,
			'pending_total_next' => $pending_total_next, 
			'partial_percent' => $partial_percent,
            'order_id' => $order_id
            );
            
            
			$totals = $this->model_account_order->getOrderTotals($this->request->get['order_id']);

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']),
				);
			}

			$data['comment'] = nl2br($order_info['comment']);

			// History
			$data['histories'] = array();

			$results = $this->model_account_order->getOrderHistories($this->request->get['order_id']);

			foreach ($results as $result) {
				$data['histories'][] = array(
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'status'     => $result['status'],
					'comment'    => $result['notify'] ? nl2br($result['comment']) : ''
				);
			}

			$data['continue'] = $this->url->link('account/order', '', true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('account/order_info', $data));
		} else {
			return new Action('error/not_found');
		}
	}

	public function reorder() {
		$this->load->language('account/order');

            
            $this->load->language('extension/total/partial_payment_total');
            
            $data['column_pending'] = $this->language->get('column_pending');
			$data['column_next_pay'] = $this->language->get('column_next_pay');

            

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info) {
			if (isset($this->request->get['order_product_id'])) {
				$order_product_id = $this->request->get['order_product_id'];
			} else {
				$order_product_id = 0;
			}

			$order_product_info = $this->model_account_order->getOrderProduct($order_id, $order_product_id);

			if ($order_product_info) {
				$this->load->model('catalog/product');

				$product_info = $this->model_catalog_product->getProduct($order_product_info['product_id']);

				if ($product_info) {
					$option_data = array();

					$order_options = $this->model_account_order->getOrderOptions($order_product_info['order_id'], $order_product_id);

					foreach ($order_options as $order_option) {
						if ($order_option['type'] == 'select' || $order_option['type'] == 'radio' || $order_option['type'] == 'image') {
							$option_data[$order_option['product_option_id']] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'checkbox') {
							$option_data[$order_option['product_option_id']][] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'text' || $order_option['type'] == 'textarea' || $order_option['type'] == 'date' || $order_option['type'] == 'datetime' || $order_option['type'] == 'time') {
							$option_data[$order_option['product_option_id']] = $order_option['value'];
						} elseif ($order_option['type'] == 'file') {
							$option_data[$order_option['product_option_id']] = $this->encryption->encrypt($this->config->get('config_encryption'), $order_option['value']);
						}
					}

					$this->cart->add($order_product_info['product_id'], $order_product_info['quantity'], $option_data);

					$this->session->data['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $product_info['product_id']), $product_info['name'], $this->url->link('checkout/cart'));

					unset($this->session->data['shipping_method']);
					unset($this->session->data['shipping_methods']);

            unset($this->session->data['partial_payment_total']);
            unset($this->session->data['pending_total']);    
            unset($this->session->data['pending_amount']);    
            unset($this->session->data['amount']);
            unset($this->session->data['pending_order_id']);
			unset($this->session->data['total_partial_payment_total_percent_dd']);
			unset($this->session->data['total_partial_payment_total_period']);
            unset($this->session->data['pending_amount_next']);
        
        
					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);
				} else {
					$this->session->data['error'] = sprintf($this->language->get('error_reorder'), $order_product_info['name']);
				}
			}
		}

		$this->response->redirect($this->url->link('account/order/info', 'order_id=' . $order_id));
	}
}