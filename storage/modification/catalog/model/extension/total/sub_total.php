<?php
class ModelExtensionTotalSubTotal extends Model {
	public function getTotal($total) {
		$this->load->language('extension/total/sub_total');

            $pending_amount = isset($this->session->data['pending_amount']) ? $this->session->data['pending_amount'] : '0';
			$pending_amount_next = isset($this->session->data['pending_amount_next']) ? $this->session->data['pending_amount_next'] : '0';
			if ($pending_amount > 0 && $this->config->get('total_partial_payment_total_tax_class_id')) {
			$pending_amount = $pending_amount;
			} else {
			$pending_amount = $pending_amount_next;
			}
			
		if (($pending_amount > 0 || $pending_amount_next > 0) && $this->config->get('total_partial_payment_total_tax_class_id')) {
			$tax = $this->tax->getTax($pending_amount, $this->config->get('total_partial_payment_total_tax_class_id')) ? $this->tax->getTax($pending_amount, $this->config->get('total_partial_payment_total_tax_class_id')) : '';
	
			$subtotal = (int)$pending_amount/((int)$tax/(int)$pending_amount+1);
			//$subtotal = $pending_amount - $tax;
			
				$total['totals'][] = array(
					'code'       => 'sub_total',
					'title'      => $this->language->get('text_sub_total'),
					'value'      => $subtotal,
					'sort_order' => $this->config->get('sub_total_sort_order')
				);
		
				$total['total'] = $pending_amount;			
			} else {
        

		$sub_total = $this->cart->getSubTotal();

		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$sub_total += $voucher['amount'];
			}
		}

		$total['totals'][] = array(
			'code'       => 'sub_total',
			'title'      => $this->language->get('text_sub_total'),
			'value'      => $sub_total,
			'sort_order' => $this->config->get('sub_total_sort_order')
		);

		$total['total'] += $sub_total;

           }
        
	}
}
