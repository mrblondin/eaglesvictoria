<?php
class ModelExtensionBaselProductTabs extends Model {
	
	public function getExtraTabsProduct($product_id){
		
		$product_tab = array();
		$categories = $this->getCategories($product_id);
		$products = $this->getProducts($product_id);
		
		// Grab all tabs
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_tabs pt 
		LEFT JOIN " . DB_PREFIX . "product_tabs_description ptd ON (pt.tab_id = ptd.tab_id) 
		LEFT JOIN " . DB_PREFIX . "product_tabs_to_product pt2p ON (pt.tab_id = pt2p.tab_id) 
		WHERE pt.status = '1' 
		AND ptd.language_id = '" . $this->config->get('config_language_id') . "' 
		ORDER BY pt.sort_order, pt.tab_id ASC");
		
			if($query->rows){
			
			foreach ($query->rows as $tab) {
				// Dont show tab by default
				$show_tab = false;
				
				// Show tab if linked via category
				if($categories){
					foreach ($categories as $category) {
						$categoriestwo = $this->getCategoriesTwo($category['category_id']);
						if($categoriestwo){
							$show_tab = true;
						}
					}
				}
				
				// Show tab if linked via product
				if($products){
					$show_tab = true;
				}
				
				// Show tab if set to show on all products
				if($tab['global']) {
					$show_tab = true;
				}
			
				
			if($show_tab){
				$product_tab[] = array(
					'tab_id' 			=> $tab['tab_id'],
					'name'				=> $tab['name'],
					'description'		=> html_entity_decode($tab['description'], ENT_QUOTES, 'UTF-8')
				);
			}
		}
		}
		return $product_tab;
	}
	
	
	public function getCategories($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		return $query->rows;
	}
	
	public function getCategoriesTwo($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_tabs_to_category WHERE category_id = '" . (int)$category_id . "'");
		return $query->rows;
	}
	
	
	
	public function getProducts($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_tabs_to_product WHERE product_id = '" . (int)$product_id . "'");
		return $query->rows;
	}
}