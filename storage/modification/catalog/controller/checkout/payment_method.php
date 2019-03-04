<?php
class ControllerCheckoutPaymentMethod extends Controller {
	public function index() {

            
            $this->load->model('extension/total/partial_payment_total');
            $this->load->model('checkout/order');
            $data['decimal_point'] = $this->language->get('decimal_point');    
            
            
            $cart_total = $this->cart->getTotal();
                
            $cart_total = $this->currency->format($cart_total, $this->session->data['currency'], '', false);

        $shipping_method = isset($this->session->data['shipping_method']) ? $this->session->data['shipping_method'] : null;
        if (isset($shipping_method['cost']) && $shipping_method['cost'] > 0) {
            $shipping = $shipping_method['cost'];
            $shipping_price = $this->currency->format($this->tax->calculate($shipping,  $shipping_method['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
        } else {
            $shipping_price = '0.00';
        }
        
            $amount_total = $cart_total + $shipping_price;
            
                $percents = explode(',', $this->config->get('total_partial_payment_total_percent'));
                //$data['partial_amount'] = '';
                $status_percent = false;
//$amount_total = $this->currency->convert( $amount_total, $this->session->data['currency'], $this->config->get('config_currency'));

                foreach ($percents as $percent) {
					$data = explode(':', $percent);
					
					if ($data[0] >= $amount_total) {
						if (isset($data[1])) {
							$data['total_partial_payment_total_percent'] = $data[1];
							$status_percent = true;
						} else {

							$status_percent = false;
						}

						break;
				}
			}	
	
              /*percent drop down*/
				  
					  
             $percents_dd = explode(',', $this->config->get('total_partial_payment_total_percent_dd'));
              
                $status_percent_dd = false;
				//$amount_total = $this->currency->convert( $amount_total, $this->session->data['currency'], $this->config->get('config_currency'));
				$data['percents_dd'] = array();
                                 
                    
                        if (isset($percents_dd)) {
                            $data['percents_dd'] = $percents_dd;
                            $status_percent_dd = true;
                        } else {

                            $status_percent_dd = false;
                        }
                       
             $data['total_partial_payment_total_percent_dd'] = isset($this->session->data['total_partial_payment_total_percent_dd']) ? $this->session->data['total_partial_payment_total_percent_dd'] : '';
			 
				
				
				
                $data['status_percent'] = $status_percent;
				$data['status_percent_dd'] = $status_percent_dd;
                $data['total_partial_payment_total_percent'] = isset($data['total_partial_payment_total_percent']) ? $data['total_partial_payment_total_percent'] : '';
                
                
            $this->load->language('extension/total/partial_payment_total');
            $data['text_partial'] = $this->language->get('text_partial');
			$data['text_period'] = $this->language->get('text_period');
			$data['text_select_period'] = $this->language->get('text_select_period');
			$data['text_partial_dd'] = $this->language->get('text_partial_dd');
            $data['text_partial_checkout'] =  sprintf($this->language->get('text_partial_checkout'), $data['total_partial_payment_total_percent']);
            $data['text_full_checkout'] = $this->language->get('text_full_checkout');
			$this->load->language('sale/order');
            
            
            $data['total_partial_payment_total_status'] = $this->config->get('total_partial_payment_total_status');
            //$data['partial'] = $this->config->get('total_partial_payment_total_status');
            if (isset($this->session->data['partial_payment_total'])) {
            $data['partial_payment_total'] = $this->session->data['partial_payment_total'];
        } else {
            $data['partial_payment_total'] = '';
        }
                    
        //$data['total'] = $this->currency->format($amount_total, $this->session->data['currency'], '', false);
		$data['total'] = $amount_total;
        $data['total_partial_payment_total_total'] = $this->config->get('total_partial_payment_total_total');
        
       /*geo zones*/
       	if (isset($this->session->data['payment_address']['country_id'])) {
			$data['country_id'] = $this->session->data['payment_address']['country_id'];
		} elseif (isset($this->session->data['shipping_address']['country_id'])) {
			$data['country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->session->data['payment_address']['zone_id'])) {
			$data['zone_id'] = $this->session->data['payment_address']['zone_id'];
		} elseif (isset($this->session->data['shipping_address']['zone_id'])) {
			$data['zone_id'] = $this->session->data['shipping_address']['zone_id'];
		} else {
			$data['zone_id'] = '';
		}
		
		
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('total_partial_payment_total_geo_zone_id') . "' AND country_id = '" . (int)$data['country_id'] . "' AND (zone_id = '" . (int)$data['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('total_partial_payment_total_geo_zone_id') == '0') {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }
		
        /* Condition for customer group */
            
        if ($status && $this->config->get('total_partial_payment_total_customer_group')) {
            if (isset($this->session->data['guest']) && in_array(0, $this->config->get('total_partial_payment_total_customer_group'))) {
                $status = true;
            } elseif ($this->customer->isLogged() && $this->session->data['customer_id']) {
                $this->load->model('account/customer');

                $customer = $this->model_account_customer->getCustomer($this->session->data['customer_id']);

                if (in_array($customer['customer_group_id'], $this->config->get('total_partial_payment_total_customer_group'))) {
                    $this->session->data['customer_group_id'] = $customer['customer_group_id'];

                    $status = true;
                } else {
                    $status = false;
                }
            } else {
                $status = false;
            }
        }
            
        /* Condition for categories and products */
        if ($status && ($this->config->get('total_partial_payment_total_category') || count(explode(',', $this->config->get('total_partial_payment_total_xproducts'))) > 0)) {
            $allowed_categories = $this->config->get('total_partial_payment_total_category');

            $xproducts = explode(',', $this->config->get('total_partial_payment_total_xproducts'));

            $cart_products = $this->cart->getProducts();

            foreach ($cart_products as $cart_product) {
                $product = array();

                if ($xproducts && in_array($cart_product['product_id'], $xproducts)) {
                    $status = false;
                    break;
                } else {
                    $product = $this->db->query("SELECT GROUP_CONCAT(`category_id`) as `categories` FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = '" . (int)$cart_product['product_id'] . "'");
                    $product = $product->row;

                    $product = explode(',', $product['categories']);

                    if ($allowed_categories){

                    if ($product && count(array_diff($product, $allowed_categories)) > 0) {
                        $status = false;
                        break;
                        }
                    }
                }
            }
        }

        $data['status'] = $status;
        
        if (isset($this->session->data['vouchers'])) {
            $data['vouchers']       = $this->session->data['vouchers'];
        }else{
            $data['vouchers'] = array();
        }

	$data['total_partial_payment_total_dd_select'] = $this->config->get('total_partial_payment_total_dd_select');
		
		/*period select*/
		$this->load->language('extension/total/partial_payment_total');
		$data['total_partial_payment_total_period'] = isset($this->session->data['total_partial_payment_total_period']) ? $this->session->data['total_partial_payment_total_period'] : '';
		
		$periods = !empty($this->config->get('total_partial_payment_total_period')) ? $this->config->get('total_partial_payment_total_period') : array();
		
		if (!empty(in_array('year', $periods))) {
		$annually = array(
		'period_id' =>'year', 
		'name' =>$this->language->get('entry_annually'));
		} else {
		$annually = '0';
		}
		if (!empty(in_array('month', $periods))) {
		$monthly = array(
		'period_id' =>'month', 
		'name' =>$this->language->get('entry_monthly'));
		} else {
		$monthly = '0';
		}
		if (!empty(in_array('week', $periods))) {
		$weekly = array(
		'period_id' =>'week', 
		'name' =>$this->language->get('entry_weekly'));
		} else {
		$weekly = '0';
		}
		
		$periods = array($annually, $monthly, $weekly);
		$data['periods'] = array_filter($periods);
		/*period select end*/
	
        
		$this->load->language('checkout/checkout');

		if (isset($this->session->data['payment_address'])) {
			// Totals
			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			// Because __call can not keep var references so we put them into an array.
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);
			
			$this->load->model('setting/extension');

			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);
					
					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			// Payment Methods
			$method_data = array();

			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('payment');

			$recurring = $this->cart->hasRecurringProducts();

			foreach ($results as $result) {
				if ($this->config->get('payment_' . $result['code'] . '_status')) {
					$this->load->model('extension/payment/' . $result['code']);

					$method = $this->{'model_extension_payment_' . $result['code']}->getMethod($this->session->data['payment_address'], $total);

					if ($method) {
						if ($recurring) {
							if (property_exists($this->{'model_extension_payment_' . $result['code']}, 'recurringPayments') && $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
								$method_data[$result['code']] = $method;
							}
						} else {
							$method_data[$result['code']] = $method;
						}
					}
				}
			}

			$sort_order = array();

			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $method_data);

			$this->session->data['payment_methods'] = $method_data;
		}

		if (empty($this->session->data['payment_methods'])) {
			$data['error_warning'] = sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact'));
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['payment_methods'])) {
			$data['payment_methods'] = $this->session->data['payment_methods'];
		} else {
			$data['payment_methods'] = array();
		}

		if (isset($this->session->data['payment_method']['code'])) {
			$data['code'] = $this->session->data['payment_method']['code'];
		} else {
			$data['code'] = '';
		}

		if (isset($this->session->data['comment'])) {
			$data['comment'] = $this->session->data['comment'];
		} else {
			$data['comment'] = '';
		}

		$data['scripts'] = $this->document->getScripts();

		if ($this->config->get('config_checkout_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

			if ($information_info) {
				$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_checkout_id'), true), $information_info['title'], $information_info['title']);
			} else {
				$data['text_agree'] = '';
			}
		} else {
			$data['text_agree'] = '';
		}

		if (isset($this->session->data['agree'])) {
			$data['agree'] = $this->session->data['agree'];
		} else {
			$data['agree'] = '';
		}

		$this->response->setOutput($this->load->view('checkout/payment_method', $data));
	}

	public function save() {
		$this->load->language('checkout/checkout');

		$json = array();

		// Validate if payment address has been set.
		if (!isset($this->session->data['payment_address'])) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', true);
		}

		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$json['redirect'] = $this->url->link('checkout/cart');

				break;
			}
		}

		if (!isset($this->request->post['payment_method'])) {
			$json['error']['warning'] = $this->language->get('error_payment');
		} elseif (!isset($this->session->data['payment_methods'][$this->request->post['payment_method']])) {
			$json['error']['warning'] = $this->language->get('error_payment');
		}

		if ($this->config->get('config_checkout_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
				$json['error']['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}

		if (!$json) {
			$this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->post['payment_method']];

			$this->session->data['comment'] = strip_tags($this->request->post['comment']);
		}

    
            $this->load->model('setting/setting');
            $this->request->post['partial_payment_total'] = isset($this->request->post['partial_payment_total']) ? $this->request->post['partial_payment_total'] : '';
			$this->request->post['total_partial_payment_total_percent_dd'] = isset($this->request->post['total_partial_payment_total_percent_dd']) ? $this->request->post['total_partial_payment_total_percent_dd'] : '';
			$this->request->post['total_partial_payment_total_period'] = isset($this->request->post['total_partial_payment_total_period']) ? $this->request->post['total_partial_payment_total_period'] : '';
			if (!$json) {
            if ($this->config->get('total_partial_payment_total_dd_select')) {
			$this->request->post['partial_payment_total'] = $this->request->post['total_partial_payment_total_percent_dd'];
			$this->session->data['partial_payment_total'] = $this->request->post['partial_payment_total'];
			$this->session->data['total_partial_payment_total_percent_dd'] = $this->request->post['total_partial_payment_total_percent_dd'];
            } else {
			$this->session->data['partial_payment_total'] = $this->request->post['partial_payment_total'];
			} 
			
			if ($this->session->data['partial_payment_total'] || $this->session->data['total_partial_payment_total_percent_dd']) {
			$this->session->data['total_partial_payment_total_period'] = $this->request->post['total_partial_payment_total_period'];
			} else {
			unset($this->session->data['total_partial_payment_total_period']);
			}
            if (($this->session->data['partial_payment_total']) && !$this->customer->islogged()) {
        
            $json['redirect'] = $this->url->link('account/login', '', true);
            }
        }
    
        
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
