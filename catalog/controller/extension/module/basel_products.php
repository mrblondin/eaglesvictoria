<?php  
class ControllerExtensionModuleBaselProducts extends Controller {
	public function index($setting) {

    	$this->load->model('catalog/product');
		$this->load->model('extension/basel/basel');
		$this->load->language('basel/basel_theme');	
  		
		$data['basel_button_quickview'] = $this->language->get('basel_button_quickview');
		$data['basel_text_sale'] = $this->language->get('basel_text_sale');
		$data['basel_text_new'] = $this->language->get('basel_text_new');
        $data['basel_text_hold'] = $this->language->get('basel_text_hold');
		$data['basel_text_out_of_stock'] = $this->language->get('basel_text_out_of_stock');
		$data['basel_text_days'] = $this->language->get('basel_text_days');
		$data['basel_text_hours'] = $this->language->get('basel_text_hours');
		$data['basel_text_mins'] = $this->language->get('basel_text_mins');
		$data['basel_text_secs'] = $this->language->get('basel_text_secs');
		
		$data['button_cart'] = $this->language->get('button_cart');
		$data['button_wishlist'] = $this->language->get('button_wishlist');
		$data['button_compare'] = $this->language->get('button_compare');
		$data['text_tax'] = $this->language->get('text_tax');
		
		// RTL support
		$data['direction'] = $this->language->get('direction');
		if ($this->language->get('direction') == 'rtl') { $data['tooltip_align'] = 'right'; } else { $data['tooltip_align'] = 'left'; }
		
		// Block title
		$data['block_title'] = $setting['use_title'];
		$data['title_preline'] = false;
		$data['title'] = false;
		$data['title_subline'] = false;
		$data['link_title'] = false;
		
		$data['contrast'] = $setting['contrast'];
		$data['items_mobile_fw'] = $this->config->get('items_mobile_fw');
		
		if (!empty($setting['title_pl'][$this->config->get('config_language_id')])) {
		$data['title_preline'] = html_entity_decode($setting['title_pl'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
		}
		if (!empty($setting['title_m'][$this->config->get('config_language_id')])) {
		$data['title'] = html_entity_decode($setting['title_m'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
		}
		if (!empty($setting['title_b'][$this->config->get('config_language_id')])) {
		$data['title_subline'] = html_entity_decode($setting['title_b'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
		}
		if (!empty($setting['link_title'][$this->config->get('config_language_id')])) {
		$data['link_title'] = html_entity_decode($setting['link_title'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
		}

        if (isset($this->request->get['filter'])) {
            $filter = $this->request->get['filter'];
        } else {
            $filter = '';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'p.sort_order';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->get['limit'])) {
            $limit = (int)$this->request->get['limit'];
        } else {
            $limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
        }

        if (isset($this->request->get['path'])) {
            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }
        } else {
            $category_id = 0;
        }
		
		$data['tabstyle'] = $setting['tabstyle'];
		$data['carousel'] = $setting['carousel'];
		$data['carousel_a'] = $setting['carousel_a'];
		$data['carousel_b'] = $setting['carousel_b'];
		$data['columns'] = $setting['columns'];
		$data['rows'] = $setting['rows'];
		$data['use_margin'] = $setting['use_margin'];
		$data['margin'] = $setting['margin'];
		$data['img_width'] = $setting['image_width'];
		$data['use_button'] = $setting['use_button'];
		$data['link_href'] = $setting['link_href'];
		$data['countdown_status'] = $setting['countdown'];	
		$data['basel_list_style'] = $this->config->get('basel_list_style');
		$data['salebadge_status'] = $this->config->get('salebadge_status');
		$data['stock_badge_status'] = $this->config->get('stock_badge_status');
		$data['basel_text_out_of_stock'] = $this->language->get('basel_text_out_of_stock');
		$data['default_button_cart'] = $this->language->get('button_cart');
		
		static $module = 0;
		
		$data['tabs'] = array();

		$this->load->model('tool/image');
		
		$tabs = $this->config->get('showintabs_tab');
		
		$tabs = isset($tabs) ? $tabs : array();

    	foreach ($tabs as $key => $tab) {
			if(in_array($key, $setting['selected_tabs']['tabs'])) {
				if (!empty($tab['title'][$this->config->get('config_language_id')])) {
					$title = $tab['title'][$this->config->get('config_language_id')];
				}else{
					$title = 'Tab';
				}	
	
				$products = array();
	
				switch ($tab['data_source']) {
					case 'SP': //Select Products
						$results = $this->getSelectProducts($tab,$setting['limit']);
						break;
					case 'PG': //Product Group
						$results = $this->getProductGroups($tab,$setting['limit']);
						break;
					case 'CQ': //Custom Query
						$results = $this->getCustomQuery($tab,$setting['limit']);
						break;
					default: // Empty
						$this->log->write('SHOW_IN_TAB::ERROR: The tab don\'t have product configured.');
						break;
				}
				
				if (isset($results)) {
				foreach ($results as $result) {
					if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['image_width'], $setting['image_height']);
					} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['image_width'], $setting['image_height']);
					}
					
					$images = $this->model_catalog_product->getProductImages($result['product_id']);
					if(isset($images[0]['image']) && !empty($images[0]['image'])){
					$images =$images[0]['image'];
				   	} else {
					$images = false;
					}
					
					if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}
							
					if ((float)$result['special']) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}
					
					if ((float)$result['special']) {
						$date_end = $this->model_extension_basel_basel->getSpecialEndDate($result['product_id']);
					} else {
						$date_end = false;
					}

					$image2 = $this->model_catalog_product->getProductImages($result['product_id']);
					if(isset($image2[0]['image']) && !empty($image2[0]['image']) && $this->config->get('basel_thumb_swap')){
						$image2 = $image2[0]['image'];
					} else {
						$image2 = false;
					}
					if ((float)$result['special']) {
						$date_end = $this->model_extension_basel_basel->getSpecialEndDate($result['product_id']);
					} else {
						$date_end = false;
					}
					if (strtotime($result['date_available']) > strtotime('-' . $this->config->get('newlabel_status') . ' day')) {
						$is_new = true;
					} else {
						$is_new = false;
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
					
					$products[] = array(
						'product_id' => $result['product_id'],
						'quantity'   => $result['quantity'],
                        'model'      => $result['model'],
						'thumb'   	 => $image,
						'thumb2' 	 => $this->model_tool_image->resize($image2, $setting['image_width'], $setting['image_height']),
						'sale_end_date'  => $date_end['date_end'],
						'name'    	 => $result['name'],
						'price'   	 => $price,
						'stock_status'      => $result['stock_status'],
						'new_label'  => $is_new,
						'special' 	 => $special,
						'tax'        => $tax,
						'minimum'    => $result['minimum'] > 0 ? $result['minimum'] : 1,
						'rating'     => $rating,
						'reviews'    => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
						'href'    	 => $this->url->link('product/product', 'product_id=' . $result['product_id']),
					);
				}
				}

				$data['tabs'][] = array(
					'title' => $title,
					'products' => $products
				);
			}
    	}

        $url = '';

        if (isset($this->request->get['filter'])) {
            $url .= '&filter=' . $this->request->get['filter'];
        }

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }
        /*
        $data['sorts'] = array();

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_default'),
            'value' => 'p.sort_order-ASC',
            'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.date_added&order=DESC' . $url)
        );

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_date_added_asc'),
            'value' => 'p.date_added-ASC',
            'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.date_added&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_date_added_desc'),
            'value' => 'p.date_added-DESC',
            'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.date_added&order=DESC' . $url)
        );

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_price_asc'),
            'value' => 'p.price-ASC',
            'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_price_desc'),
            'value' => 'p.price-DESC',
            'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=DESC' . $url)
        );

        $url = '';

        if (isset($this->request->get['filter'])) {
            $url .= '&filter=' . $this->request->get['filter'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $data['limits'] = array();

        $limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

        sort($limits);

        foreach($limits as $value) {
            $data['limits'][] = array(
                'text'  => $value,
                'value' => $value,
                'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&limit=' . $value)
            );
        }

        $url = '';

        if (isset($this->request->get['filter'])) {
            $url .= '&filter=' . $this->request->get['filter'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&page={page}');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

        // http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
        if ($page == 1) {
            $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id']), 'canonical');
        } else {
            $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page='. $page), 'canonical');
        }

        if ($page > 1) {
            $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . (($page - 2) ? '&page='. ($page - 1) : '')), 'prev');
        }

        if ($limit && ceil($product_total / $limit) > $page) {
            $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page='. ($page + 1)), 'next');
        }*/

        $data['sort'] = $sort;
        $data['order'] = $order;
        $data['limit'] = $limit;
		
    	$data['button_cart'] = $this->language->get('button_cart');
		
		$data['module'] = $module++;

		if ($this->config->get('theme_default_directory') == 'basel')
		return $this->load->view('extension/module/basel_products', $data);
		
  	}

  	private function getProductGroups( $tabInfo , $limit ){
  		$results = array();

  		switch ( $tabInfo['product_group'] ) {
  			case 'BS':
  				$results = $this->model_catalog_product->getBestSellerProducts($limit);
  				break;
  			case 'LA':
  				$results = $this->model_catalog_product->getLatestProducts($limit);
  				break;
  			case 'SP':
  				$results = $this->model_catalog_product->getProductSpecials(array('start' => 0,'limit' => $limit));
  				break;
  			case 'PP':
  				$results = $this->model_catalog_product->getPopularProducts($limit);
  				break;
  		}
  		return $results;
  	}

  	private function getSelectProducts( $tabInfo , $limit ){
  		$results = array();

  		if(isset($tabInfo['products'])){
  			$limit_count = 0;
			foreach ( $tabInfo['products'] as $product ) {
				if ($limit_count++ == $limit) break;
				$product_info = $this->model_catalog_product->getProduct($product['product_id']);
				if ($product_info) {
					$results[$product['product_id']] = $this->model_catalog_product->getProduct($product['product_id']);
				}
			}
		}

		return $results;

  	}

  	private function getCustomQuery( $tabInfo , $limit){
  		$results = array();

  		if ( $tabInfo['sort'] == 'rating' || $tabInfo['sort'] == 'p.date_added') {
  			$order = 'DESC';
  		}else{
  			$order = 'ASC';
  		}

  		$data = array(
  			'filter_category_id' => $tabInfo['filter_category']=='ALL' ? '' : $tabInfo['filter_category'], 
  			'filter_manufacturer_id' => $tabInfo['filter_manufacturer']=='ALL' ? '' : $tabInfo['filter_manufacturer'], 
  			'sort' => $tabInfo['sort'], 
  			'order' => $order,
  			'start' => 0,
  			'limit' => $limit
  		);

  		$results = $this->model_catalog_product->getProducts($data);

		return $results;
  	}

}