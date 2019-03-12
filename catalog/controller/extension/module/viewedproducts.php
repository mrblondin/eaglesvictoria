<?php
class ControllerExtensionModuleViewedproducts extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/viewedproducts');

		$data['heading_title'] = $this->language->get('heading_title');
		$data['carousel'] = $setting['carousel'];
		$data['slidesPerView'] = $setting['slidesPerView'];
        $data['stock_badge_status'] = $this->config->get('stock_badge_status');

		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}
		if (isset($parts[0])) {
			$data['category_id'] = $parts[0];
		} else {
			$data['category_id'] = 0;
		}
		if (isset($parts[1])) {
			$data['child_id'] = $parts[1];
		} else {
			$data['child_id'] = 0;
		}

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');
		
		$this->load->model('tool/image');

		if(isset($this->session->data['products_id']))
			$this->session->data['products_id'] = array_slice($this->session->data['products_id'], -$setting['limit']);

		if (isset($this->session->data['products_id'])) {
			foreach ($this->session->data['products_id'] as $result_id) {
				$result = $this->model_catalog_product->getProduct($result_id);

				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}

                if (strtotime($result['date_available']) > strtotime('-' . $this->config->get('newlabel_status') . ' day')) {
                    $is_new = true;
                } else {
                    $is_new = false;
                }

				$data['products'][] = array(
					'product_id'  => $result['product_id'],
                    'quantity'  => $result['quantity'],
					'thumb'       => $image,
					'name'        => $result['name'],
                    'stock_status'      => $result['stock_status'],
                    'stock_status_id'      => $result['stock_status_id'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $rating,
                    'new_label'  => $is_new,
                    'model'       => $result['model'],
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])

				);
			}
		}

        // Basel start
        $this->load->model('extension/basel/basel');
        $this->load->language('basel/basel_theme');
        $data['basel_button_quickview'] = $this->language->get('basel_button_quickview');
        $data['basel_text_sale'] = $this->language->get('basel_text_sale');
        $data['basel_text_new'] = $this->language->get('basel_text_new');
        $data['basel_text_days'] = $this->language->get('basel_text_days');
        $data['basel_text_hours'] = $this->language->get('basel_text_hours');
        $data['basel_text_mins'] = $this->language->get('basel_text_mins');
        $data['basel_text_secs'] = $this->language->get('basel_text_secs');
        $data['category_thumb_status'] = $this->config->get('category_thumb_status');
        $data['category_subs_status'] = $this->config->get('category_subs_status');
        $data['countdown_status'] = $this->config->get('countdown_status');
        $data['salebadge_status'] = $this->config->get('salebadge_status');
        $data['basel_subs_grid'] = $this->config->get('basel_subs_grid');
        $data['basel_prod_grid'] = $this->config->get('basel_prod_grid');
        $data['basel_list_style'] = $this->config->get('basel_list_style');
        $data['stock_badge_status'] = $this->config->get('stock_badge_status');
        $data['basel_text_out_of_stock'] = $this->language->get('basel_text_out_of_stock');
        $data['default_button_cart'] = $this->language->get('button_cart');
        $data['direction'] = $this->language->get('direction');
        if ($this->language->get('direction') == 'rtl') { $data['tooltip_align'] = 'right'; } else { $data['tooltip_align'] = 'left'; }
        // Basel end

			return $this->load->view('extension/module/viewedproducts', $data);
		
	}
}