<?php

	$data['full_width_tabs'] = $this->config->get('full_width_tabs');
	$data['product_tabs_style'] = $this->config->get('product_tabs_style');
	$data['img_w'] = $this->config->get('theme_default_image_thumb_width');
	$data['img_h'] = $this->config->get('theme_default_image_thumb_height');
	$data['img_a_w'] = $this->config->get('theme_default_image_additional_width');
	$data['img_a_h'] = $this->config->get('theme_default_image_additional_height');
	$data['meta_description_status'] = $this->config->get('meta_description_status');
	$data['product_page_countdown'] = $this->config->get('product_page_countdown');
	$data['meta_description'] = $product_info['meta_description'];	
	$data['product_layout'] = $this->config->get('product_layout');
	$data['qty'] = $product_info['quantity'];
	$data['basel_price_update'] = $this->config->get('basel_price_update');
	$data['basel_sharing_style'] = $this->config->get('basel_sharing_style');
	$data['review_qty'] = $product_info['reviews'];
	$data['currency_code'] = $this->session->data['currency'];
	$data['button_reviews'] = $this->language->get('button_reviews');
	$data['currency_code'] = $this->session->data['currency'];
	$data['hover_zoom'] = $this->config->get('basel_hover_zoom');
	$data['current_href'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);
	$this->document->addLink($data['thumb'], 'image');
	$data['basel_share_btn'] = $this->config->get('basel_share_btn');
	$data['basel_rel_prod_grid'] = $this->config->get('basel_rel_prod_grid');
	$data['items_mobile_fw'] = $this->config->get('items_mobile_fw');
	if (strtotime($product_info['date_available']) > strtotime('-' . $this->config->get('newlabel_status') . ' day')) $data['is_new'] = true;
	$data['basel_text_offer_ends'] = $this->language->get('basel_text_offer_ends');
	$data['price_snippet'] = preg_replace("/[^0-9,.]/","", $data['price'] );
	if ((float)$product_info['special']) {
	$date_end = $this->model_extension_basel_basel->getSpecialEndDate($product_info['product_id']);
	$data['sale_end_date'] = $date_end['date_end'];
	$data['special_snippet'] = preg_replace("/[^0-9,.]/","", $data['special']);
	}
	
	// RTL support
	$data['direction'] = $this->language->get('direction');
	
	// Product Questions and Answers
	$data['question_status'] = $this->config->get('product_question_status');
	$data['product_questions'] = $this->load->controller('extension/basel/question');
	$data['basel_tab_questions'] = $this->language->get('basel_tab_questions');
	$data['basel_button_ask'] = $this->language->get('basel_button_ask');
	$this->load->model('extension/basel/question');
	$data['questions_total'] = $this->model_extension_basel_question->getTotalQuestionsByProductId($this->request->get['product_id']);
	
	// Product Tabs
	$this->load->model('extension/basel/product_tabs');
	$data['product_tabs'] = $this->model_extension_basel_product_tabs->getExtraTabsProduct($this->request->get['product_id']);
			