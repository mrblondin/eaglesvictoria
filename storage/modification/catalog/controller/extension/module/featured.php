<?php

    global $aFolder;
    global $modulesPath;
    
    
    if (!defined('HTTP_ADMIN')) {
        $root_dir = DIR_APPLICATION.'../';
        $folder_contents = scandir($root_dir);
                if (!(in_array('admin', $folder_contents) && file_exists($root_dir.'admin/config.php'))) {
                        foreach ($folder_contents as $value) {
                                if (is_dir($root_dir.$value) && $value != '.' && $value != '..'){
                                        if (file_exists($root_dir.$value.'/config.php')) {
                                                $admin_folder_name = $value;
                                                continue;
                                        }
                                }
                        }
                }
        if (isset($admin_folder_name)) {
                define('HTTP_ADMIN',$admin_folder_name);
        } else {
                define('HTTP_ADMIN','admin');
        }  
    }
    
    $aFolder = preg_replace('/.*\/([^\/].*)\//is','$1',HTTP_ADMIN);
    
    if (version_compare(VERSION,'2.3','>=')) { //newer than 2.2.x
        $modulesPath = 'extension/module';
    } else {
        $modulesPath = 'module';
    }
    
    
    
    include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",__FILE__,$matches)?'components/com_'.$matches[1].'/opencart/':'').$aFolder.'/controller/'.$modulesPath.'/magictoolbox-module.inc';
    
class ControllerExtensionModuleFeatured extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/featured');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$data['products'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if (!empty($setting['product'])) {
			$products = array_slice($setting['product'], 0, (int)$setting['limit']);

			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

                            $product_infos[] = $product_info;
                        

				if ($product_info) {
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']).'" id="featured_'.$product_info['product_id'].'"';
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$product_info['special']) {
						$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $product_info['rating'];
					} else {
						$rating = false;
					}

					$data['products'][] = array(
						'product_id'  => $product_info['product_id'],
						'thumb'       => $image,
						'name'        => $product_info['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
					);
				}
			}
		}

		if ($data['products']) {
			$contents = $this->load->view('extension/module/featured', $data);
                            global $aFolder;
                            global $modulesPath;
                            
                            if (isset($GLOBALS['magictoolbox_modules'])) { 
                                        global $tools;

                                        foreach ($tools as $tool) {
                                            if (isset($GLOBALS['magictoolbox_modules'][$tool])) {
                                                $boxModule = $GLOBALS['magictoolbox_modules'][$tool];
                                                if ($boxModule['status']) { //if module enabled
                                                    if (!function_exists($tool)) include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",__FILE__,$matches)?'components/com_'.$matches[1].'/opencart/':'').$aFolder.'/controller/'.$modulesPath.'/'.$tool.'-opencart-module/module.php';
                                                    $boxPath = (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",__FILE__,$matches)?'components/com_'.$matches[1].'/opencart/':'').$aFolder.'/controller/'.$modulesPath.'/'.$tool.'-opencart-module/boxes.inc'; 
                                                    if (file_exists($boxPath)) {
                                                        include ($boxPath);
                                                    }
                                                }
                                            }
                                        }
                                    }
                            return $contents;
		}
	}
}