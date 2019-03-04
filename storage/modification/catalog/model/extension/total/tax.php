<?php
class ModelExtensionTotalTax extends Model {
	public function getTotal($total) {

            $pending_amount = isset($this->session->data['pending_amount']) ? $this->session->data['pending_amount'] : '0';
			$pending_amount_next = isset($this->session->data['pending_amount_next']) ? $this->session->data['pending_amount_next'] : '0';
			if ($pending_amount > 0 && $this->config->get('total_partial_payment_total_tax_class_id')) {
			$pending_amount = $pending_amount;
			} else {
			$pending_amount = $pending_amount_next;
			}
		if (($pending_amount > 0 || $pending_amount_next > 0) && $this->config->get('total_partial_payment_total_tax_class_id')) {
		   $total['taxes'] = $this->tax->getRates($pending_amount, $this->config->get('total_partial_payment_total_tax_class_id'));
		
		   
		   foreach ($total['taxes'] as $key => $value) {
		   
		    $total['tax'] = (int)$pending_amount - (int)$pending_amount/((int)$value['amount']/(int)$pending_amount+1);
		   
			if ($value['amount'] > 0) {
				$total['totals'][] = array(
					'code'       => 'tax',
					'title'      => $this->tax->getRateName($key),
					'value'      => $total['tax'],
					'sort_order' => $this->config->get('tax_sort_order')
				);
			}
		   }
		} else {
        
		foreach ($total['taxes'] as $key => $value) {
			if ($value > 0) {
				$total['totals'][] = array(
					'code'       => 'tax',
					'title'      => $this->tax->getRateName($key),
					'value'      => $value,
					'sort_order' => $this->config->get('total_tax_sort_order')
				);

				$total['total'] += $value;

           }
        
			}
		}
	}
}