<?php
/* Partial Payment Total for OpenCart v.2.3.x 
 *
 * @version 2.3.0
 * @date 16/08/2018
 * @author Kestutis Banisauskas
 * @Smartechas
 */
class ControllerExtensionTotalPartialPaymentTotalSubtotal extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/total/partial_payment_total_subtotal');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('total_partial_payment_total_subtotal', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total'));
		}
		
		$this->document->setTitle($this->language->get('heading_title_main'));
		$data['heading_title'] = $this->language->get('heading_title_main');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/total/partial_payment_total_subtotal', 'user_token=' . $this->session->data['user_token'])
		);

		$data['action'] = $this->url->link('extension/total/partial_payment_total_subtotal', 'user_token=' . $this->session->data['user_token']);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total');

		if (isset($this->request->post['total_partial_payment_total_subtotal_status'])) {
			$data['total_partial_payment_total_subtotal_status'] = $this->request->post['total_partial_payment_total_subtotal_status'];
		} else {
			$data['total_partial_payment_total_subtotal_status'] = $this->config->get('total_partial_payment_total_subtotal_status');
		}

		if (isset($this->request->post['total_partial_payment_total_subtotal_sort_order'])) {
			$data['total_partial_payment_total_subtotal_sort_order'] = $this->request->post['total_partial_payment_total_subtotal_sort_order'];
		} else {
			$data['total_partial_payment_total_subtotal_sort_order'] = $this->config->get('total_partial_payment_total_subtotal_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/total/partial_payment_total_subtotal', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/total/partial_payment_total_subtotal')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}