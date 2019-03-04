<?php
class ModelExtensionModuleCurrencyUpdate extends Model {
	
	protected $log_instance;
	
	public function __construct($registry) {
		parent::__construct($registry);
		
		error_reporting(-1);
		ini_set('display_errors', 1);
		$this->log_instance = new Log('currency_update.log');
	}
	
	public function update($force = false) {
		
		if ($this->config->get('module_currency_update_status') == 0) {
			return false;
		}

		if ($this->config->get('module_currency_update_autoupdate') == 1 || $force) {
			
			$comission = $this->config->get('module_currency_update_comission') > 0 ? (float) $this->config->get('module_currency_update_comission') / 100 : false;
			$base_currency = $this->config->get('config_currency');
			
			$sql = "SELECT * FROM " . DB_PREFIX . "currency WHERE code != '" . $this->db->escape($this->config->get('config_currency')) . "'";
		
			if (!$force) {
				$sql .= " AND date_modified < '" .  $this->db->escape(date('Y-m-d H:i:s', strtotime('-1 hour'))) . "'";
			}
			
			$query = $this->db->query($sql);
			
			$currency_codes = array();

			foreach ($query->rows as $result) {
				$currency_codes[] = $result['code'];
			}
			
			// fixer.io
			if ($this->config->get('module_currency_update_source') == 'fixer.io' && sizeof($currency_codes) > 0) {
				
				$response = $this->curlRequest('http://data.fixer.io/api/latest?access_key=3d2195a309d60ed3d6d745464acebb93&base='.$base_currency.'&symbols='.implode(',', $currency_codes));

				if ($response) {
					$json = json_decode($response);
					
					foreach ($currency_codes as $code) {
						$value = $comission ? (float) $json->rates->{$code} + ((float) $json->rates->{$code} * $comission) : $json->rates->{$code};
						$this->db->query("UPDATE " . DB_PREFIX . "currency SET value = '" . $this->db->escape($value) . "', date_modified = '" .  $this->db->escape(date('Y-m-d H:i:s')) . "' WHERE code = '" . $this->db->escape($code) . "'");
					}
					
					$this->db->query("UPDATE " . DB_PREFIX . "currency SET value = '1.00000', date_modified = '" .  $this->db->escape(date('Y-m-d H:i:s')) . "' WHERE code = '" . $this->db->escape($base_currency) . "'");
				} else {
					return false;
				}
				
			// alphavantage.co
			} elseif ($this->config->get('module_currency_update_source') == 'alphavantage.co') {
				
				$api_key = $this->config->get('module_currency_update_alphavantage_api_key');
				
				foreach ($currency_codes as $code) {
					$response = $this->curlRequest('https://www.alphavantage.co/query?function=CURRENCY_EXCHANGE_RATE&from_currency='.$base_currency.'&to_currency='.$code.'&apikey='.$api_key);
					
					if ($response) {
						$json = json_decode($response);
						
						if (@is_null($json->{"Error Message"})) {
							$value = (float) $json->{"Realtime Currency Exchange Rate"}->{"5. Exchange Rate"};
							$value = $comission ? $value + ($value * $comission) : $value;
							$this->db->query("UPDATE " . DB_PREFIX . "currency SET value = '" . $this->db->escape($value) . "', date_modified = '" .  $this->db->escape(date('Y-m-d H:i:s')) . "' WHERE code = '" . $this->db->escape($code) . "'");
						} else {
							$this->log('alphavantage error : '.$json->{"Error Message"});
							return false;
						}
					} else {
						return false;
					}
				}
				
				$this->db->query("UPDATE " . DB_PREFIX . "currency SET value = '1.00000', date_modified = '" .  $this->db->escape(date('Y-m-d H:i:s')) . "' WHERE code = '" . $this->db->escape($base_currency) . "'");
	
			// xe.com
			} elseif ($this->config->get('module_currency_update_source') == 'xe.com') {
				
				foreach ($currency_codes as $code) {
					$response = $this->curlRequest('http://xe.com/currencyconverter/convert.cgi?template=pca-new&Amount=1&From='.$base_currency.'&To='.$code.'&image.x=64&image.y=8&image=Submit');
					
					if ($response) {
						$dom = new DomDocument();
						$internalErrors = libxml_use_internal_errors(true);
						$dom->loadHTML($response);
						$xpath = new DomXPath($dom);
						$nodes = $xpath->query('//span[@class="XEenlarge"]');
						
						$value = (float) trim(str_replace(array($code, ','), '', $nodes->item(2)->nodeValue));
						
						$value = $comission ? $value + ($value * $comission) : $value;
						$this->db->query("UPDATE " . DB_PREFIX . "currency SET value = '" . $this->db->escape($value) . "', date_modified = '" .  $this->db->escape(date('Y-m-d H:i:s')) . "' WHERE code = '" . $this->db->escape($code) . "'");
						
						libxml_use_internal_errors($internalErrors);
					} else {
						$this->log('xe.com error : no response from curl');
						return false;
					}
				}
				
				$this->db->query("UPDATE " . DB_PREFIX . "currency SET value = '1.00000', date_modified = '" .  $this->db->escape(date('Y-m-d H:i:s')) . "' WHERE code = '" . $this->db->escape($base_currency) . "'");
			} else {
				return false;
			}
			
			$this->cache->delete('currency');
			
			return true;
		}
	}
	
	private function curlRequest($url, $options = array()) {
		
		$this->log('Curl init : '.$url);
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		
		if (curl_error($ch)) {
			$this->log('Curl error : '.curl_error($ch));
			return false;
		}
		
		if (in_array($info['http_code'], array(401,403,404))) {
			$this->log('Curl error : '.$info['http_code'].' header status');
			return false;
		}
		
		return $result;
	}
	
	private function log($str) {
		if ($this->config->get('module_currency_update_debug') == 1) {
			$this->log_instance->write($str);
		}
	}

}