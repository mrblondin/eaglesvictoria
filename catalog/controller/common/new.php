<?php
class ControllerCommonNew extends Controller {
    public function index() {
        $this->load->language('product/category');

        $this->load->model('catalog/category');

        $this->load->model('catalog/product');
        $this->load->model('extension/basel/basel');

        $this->load->model('tool/image');

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

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => "New Items",
            'href' => $this->url->link('common/new', '', true)
        );

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

            $path = '';

            $parts = explode('_', (string)$this->request->get['path']);

            $category_id = (int)array_pop($parts);

            foreach ($parts as $path_id) {
                if (!$path) {
                    $path = (int)$path_id;
                } else {
                    $path .= '_' . (int)$path_id;
                }

                $category_info = $this->model_catalog_category->getCategory($path_id);

                if ($category_info) {
                    //$data['breadcrumbs'][] = array(
                    //    'text' => $category_info['name'],
                    //    'href' => $this->url->link('product/category', 'path=' . $path . $url)
                    //);
                }
            }
        } else {
            $category_id = 0;
        }

        $category_info = $this->model_catalog_category->getCategory($category_id);

        if (!$category_info) {
            $data['basel_prod_grid'] = 4;
            $data['default_button_cart'] = 'Add To Cart';
            $this->document->setTitle("New Items");
            $this->document->setDescription("New Items");
            $this->document->setKeywords("New Items");

            $data['heading_title'] = "New Items";

            $data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

            // Set the last category breadcrumb
            //$data['breadcrumbs'][] = array(
            //    'text' => $category_info['name'],
            //    'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'])
            //);

            //if ($category_info['image']) {
            //    $data['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') //. '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
            //} else {
            //    $data['thumb'] = '';
            //}

            //$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
            $data['compare'] = $this->url->link('product/compare');

            $url = '';

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            $data['products'] = array();

            $filter_data = array(
                //'filter_category_id' => $category_id,
                'filter_filter'      => $filter,
                //'sort'               => 'p.date_added',
                //'order'              => 'DESC',
                'sort'               => $sort,
                'order'              => $order,
                'start'              => ($page - 1) * $limit,
                'limit'              => $limit
                //'last'               => 5
            );

            $product_total = $this->model_catalog_product->getTotalProducts($filter_data);

            $results = $this->model_catalog_product->getProducts($filter_data);


            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
                /*if (isset($this->request->get['order'])) {
                    if($this->request->get['order'] == 'ASC'){
                       $this->array_sort_by_column($results, substr($this->request->get['sort'], 2), SORT_ASC); }
                        else {$this->array_sort_by_column($results, substr($this->request->get['sort'], 2), SORT_DESC);  }
                }*/
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            //$data['categories'] = array();

            //$results = $this->model_catalog_category->getCategories($category_id);

            //foreach ($results as $result) {
            //    $filter_data = array(
            //        'filter_category_id'  => $result['category_id'],
            //        'filter_sub_category' => true
            //    );

            //    $data['categories'][] = array(
            //        'name' => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
            //        'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url)
            //    );
            //}

            foreach ($results as $result) {
                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                }

                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $price = false;
                }


                $image2 = $this->model_catalog_product->getProductImages($result['product_id']);
                if(isset($image2[0]['image']) && !empty($image2[0]['image']) && $this->config->get('basel_thumb_swap')){
                    if (isset($this->request->get['route']) == 'product/product' && isset($this->request->get['product_id'])) {
                        $image2 = $this->model_tool_image->resize($image2[0]['image'], $this->config->get('theme_default_image_related_width'), $this->config->get('theme_default_image_related_height'));
                    } else {
                        $image2 = $this->model_tool_image->resize($image2[0]['image'], $this->config->get('theme_default_image_product_width'), $this->config->get('theme_default_image_product_height'));
                    }
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
                    $rating = (int)$result['rating'];
                } else {
                    $rating = false;
                }

                $data['products'][] = array(
                    'product_id'  => $result['product_id'],
                    'thumb2'  => $image2,
                    'quantity'  => $result['quantity'],
                    'model'  => $result['model'],
                    'sale_end_date'  => $date_end['date_end'],
                    'new_label'  => $is_new,
                    'stock_status'      => $result['stock_status'],
                    'stock_status_id'      => $result['stock_status_id'],
                    'thumb'       => $image,
                    'name'        => $result['name'],
                    'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                    'price'       => $price,
                    'special'     => $special,
                    'tax'         => $tax,
                    'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
                    'rating'      => $result['rating'],
                    'href'        => $this->url->link('product/product', '&product_id=' . $result['product_id'] . $url)
                );
            }

            $url = '';

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $data['sorts'] = array();

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_default'),
                'value' => 'p.sort_order-ASC',
                'href'  => $this->url->link('common/new', '&sort=p.date_added&order=DESC' . $url)
            );

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_date_added_asc'),
                'value' => 'p.date_added-ASC',
                'href'  => $this->url->link('common/new', '&sort=p.date_added&order=ASC' . $url)
            );

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_date_added_desc'),
                'value' => 'p.date_added-DESC',
                'href'  => $this->url->link('common/new', '&sort=p.date_added&order=DESC' . $url)
            );

            /*
			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=DESC' . $url)
			);*/

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_price_asc'),
                'value' => 'p.price-ASC',
                'href'  => $this->url->link('common/new', '&sort=p.price&order=ASC' . $url)
            );

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_price_desc'),
                'value' => 'p.price-DESC',
                'href'  => $this->url->link('common/new', '&sort=p.price&order=DESC' . $url)
            );

            if ($this->config->get('config_review_status')) {
                $data['sorts'][] = array(
                    'text'  => $this->language->get('text_rating_desc'),
                    'value' => 'rating-DESC',
                    'href'  => $this->url->link('common/new', '&sort=rating&order=DESC' . $url)
                );

                $data['sorts'][] = array(
                    'text'  => $this->language->get('text_rating_asc'),
                    'value' => 'rating-ASC',
                    'href'  => $this->url->link('common/new',  '&sort=rating&order=ASC' . $url)
                );
            }
            /*
			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=DESC' . $url)
			);*/

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
                    'href'  => $this->url->link('common/new', $url . '&limit=' . $value)
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
            $pagination->url = $this->url->link('common/new', $url . '&page={page}');

            $data['pagination'] = $pagination->render();

            $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

            // http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
            if ($page == 1) {
                $this->document->addLink($this->url->link('common/new', 'path='), 'canonical');
            } else {
                $this->document->addLink($this->url->link('common/new', '&page='. $page), 'canonical');
            }

            if ($page > 1) {
                $this->document->addLink($this->url->link('common/new', (($page - 2) ? '&page='. ($page - 1) : '')), 'prev');
            }

            if ($limit && ceil($product_total / $limit) > $page) {
                $this->document->addLink($this->url->link('common/new', '&page='. ($page + 1)), 'next');
            }

            $data['sort'] = $sort;
            $data['order'] = $order;
            $data['limit'] = $limit;

            $data['continue'] = $this->url->link('common/home');

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

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('product/category', $data));
        } else {
            $url = '';

            if (isset($this->request->get['path'])) {
                $url .= '&path=' . $this->request->get['path'];
            }

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            //$data['breadcrumbs'][] = array(
            //    'text' => $this->language->get('text_error'),
            //    'href' => $this->url->link('product/category', $url)
            //);

            $this->document->setTitle("New Items");

            $data['continue'] = $this->url->link('common/home');

            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }

    public function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
        $sort_col = array();
        foreach ($arr as $key=> $row) {
            $sort_col[$key] = $row[$col];
        }

        array_multisort($sort_col, $dir, $arr);
    }
}
