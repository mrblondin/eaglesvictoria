<?php 

/**
 * Maksuturva Payment Module for osCommerce 2.3.x
 * Module developed by
 * 	RunWeb Desenvolvimento de Sistemas LTDA and
 *  Movutec Oy
 *
 * www.runweb.com.br
 * www.movutec.com
 */
class ControllerExtensionPaymentMaksuturva extends Controller {
 	private $error = array(); 
	
	public function index() {
		$this->load->language('extension/payment/maksuturva');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting('payment_maksuturva', $this->request->post);				
		  	
			$this->session->data['success'] = $this->language->get('text_success');
		  	
			$this->response->redirect($this->getLink('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL'));		
		}
		$data['heading_title'] = $this->language->get('heading_title');
        $data['text_verify'] = $this->language->get('text_verify');
        $data['text_kauppias'] = $this->language->get('text_kauppias');
        $data['verify_link'] = $this->getLink('extension/payment/maksuturva/verify', 'user_token=' . $this->session->data['user_token'], 'SSL');
        // default fields:
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_none'] = $this->language->get('text_none');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');		


		// Errors
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['sellerid'])) {
            $data['error_sellerid'] = $this->error['sellerid'];
        } else {
            $data['error_sellerid'] = '';
        }

        if (isset($this->error['secretkey'])) {
            $data['error_secretkey'] = $this->error['secretkey'];
        } else {
            $data['error_secretkey'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'href' => $this->getLink('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'text' => $this->language->get('text_home'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'href' => $this->getLink('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'text' => $this->language->get('text_payment'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'href' => $this->getLink('extension/payment/maksuturva/verify', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'text' => $this->language->get('heading_maksuturva'),
            'separator' => ' :: '
        );
        $data['breadcrumbs'][] = array(
            'href' => $this->getLink('extension/payment/maksuturva', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'text' => $this->language->get('heading_title'),
            'separator' => ' :: '
        );


        $data['action'] = $this->getLink('extension/payment/maksuturva', 'user_token=' . $this->session->data['user_token'], 'SSL');

        $data['cancel'] = $this->getLink('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');

        /*
         * Starting custom fields
         */
		
		$custom_fields = array("created", "completed", "cancelled", "error", "delayed", 
				"sandbox", "sellerid", "secretkey", "keyversion", "url", "status", "sort_order", "emaksut", "encoding");
		
		foreach ($custom_fields as $field) {
            if (isset($this->request->post["payment_maksuturva_$field"])) {
                $data["payment_maksuturva_$field"] = $this->request->post["payment_maksuturva_$field"];
            } else {
                $data["payment_maksuturva_$field"] = $this->config->get("payment_maksuturva_$field");
            }
            $data["entry_$field"] = $this->language->get("entry_$field");
        }

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();


        $this->template = 'extension/payment/maksuturva';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/maksuturva', $data));
	}
	
	private function validate()
    {

        if (!$this->user->hasPermission('modify', 'extension/payment/maksuturva')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (!$this->request->post['payment_maksuturva_sandbox']) {
            if (!$this->request->post['payment_maksuturva_sellerid']) {
                $this->error['sellerid'] = $this->language->get('error_sellerid');
            }

            if (!$this->request->post['payment_maksuturva_secretkey']) {
                $this->error['secretkey'] = $this->language->get('error_secretkey');
            }
        }
        if (!$this->error) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
	
	
  	/**
     * Method which verifies all the pending payments
     * (delay payment) returned from maksuturva.
     */
    public function verify()
    {
        // Load sale/order model to get orders
        $this->load->model('sale/order');
        // Default methods from OpenCart admin template system
        $this->load->language('extension/payment/maksuturva');
        $this->document->setTitle($this->language->get('heading_maksuturva'));
        $data['text_verifylist'] = $this->language->get('text_verifylist');
        $data['text_order'] = $this->language->get('text_order');
        $data['text_message'] = $this->language->get('text_message');
        $data['heading_title'] = $this->language->get('heading_maksuturva');
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'href' => $this->getLink('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'text' => $this->language->get('text_home'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'href' => $this->getLink('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'text' => $this->language->get('text_payment'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'href' => $this->getLink('extension/payment/maksuturva', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'text' => $this->language->get('heading_title'),
            'separator' => ' :: '
        );
        $data['breadcrumbs'][] = array(
            'href' => $this->getLink('extension/payment/maksuturva/verify', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'text' => $this->language->get('heading_maksuturva'),
            'separator' => ' :: '
        );
        if (!$this->user->hasPermission('modify', 'sale/order')) {
            return;
        }
        // load orders from database, considering created and delayed ones
        $sort = "order_status_id";
        $filter_order_status_id = $this->config->get("payment_maksuturva_created");
        $data1 = array(
            'filter_order_status_id' => $filter_order_status_id,
            'sort' => $sort,
        );
        $created_orders = $this->model_sale_order->getOrders($data1);

        $filter_order_status_id = $this->config->get("payment_maksuturva_delayed");
        $data1 = array(
            'filter_order_status_id' => $filter_order_status_id,
            'sort' => $sort,
        );
        $delayed_orders = $this->model_sale_order->getOrders($data1);

        $orders = array_merge($delayed_orders, $created_orders);

        require_once dirname(__FILE__) . '../../../../../catalog/controller/extension/payment/MaksuturvaGateway/MaksuturvaGatewayImplementation.php';

        // check each one with Maksuturva
        $statuses = array();
        $links = array();
        foreach ($orders as $order) {
            // ignore non-maksuturva payments:

            $id = $order['order_id'];
            $links[$id] = $this->getLink('sale/order/info', 'order_id=' . $id . '&user_token=' . $this->session->data['user_token'], 'SSL');
            $order_info = $this->model_sale_order->getOrder($id);
            if ($order_info["payment_method"] != "Maksuturva/eMaksut") continue;
            //$this->model_sale_order->editOrder($this->request->get['order_id'], $this->request->post);
            try {
                $gateway = new MaksuturvaGatewayImplementation($order_info, $this, $this->config->get("payment_maksuturva_encoding"));
                $response = $gateway->statusQuery();
            } catch (Exception $e) {
                // probably error about no CURL installed
                $statuses[$id] = $this->language->get('ERROR_QUERY');
                continue;
            }
            if ($response === false) {
                $statuses[$id] = $this->language->get('ERROR_RESPONSE_FALSE');
                continue;
            }

            switch ($response["pmtq_returncode"]) {
                // set as paid if not already set
                case MaksuturvaGatewayImplementation::STATUS_QUERY_PAID:
                case MaksuturvaGatewayImplementation::STATUS_QUERY_PAID_DELIVERY:
                case MaksuturvaGatewayImplementation::STATUS_QUERY_COMPENSATED:
                    $statuses[$id] = $this->language->get('PAYMENT_IDENTIFIED') . ' (' . $response["pmtq_returntext"] . ')';
                    $data1 = array(
                        "notify" => true,
                        "order_id" => $id,
                        "order_status_id" => $this->config->get("payment_maksuturva_completed"),
                        "comment" => $statuses[$id],
                        'append' => true,
                    );
                    $this->model_sale_order->addOrderHistory($id, $data1);
                    break;


                // set payment cancellation with the notice
                // stored in response_text
                case MaksuturvaGatewayImplementation::STATUS_QUERY_PAYER_CANCELLED:
                case MaksuturvaGatewayImplementation::STATUS_QUERY_PAYER_CANCELLED_PARTIAL:
                case MaksuturvaGatewayImplementation::STATUS_QUERY_PAYER_CANCELLED_PARTIAL_RETURN:
                case MaksuturvaGatewayImplementation::STATUS_QUERY_PAYER_RECLAMATION:
                case MaksuturvaGatewayImplementation::STATUS_QUERY_CANCELLED:
                    $statuses[$id] = $this->language->get('PAYMENT_CANCELLED') . ' (' . $response["pmtq_returntext"] . ')';
                    $data1 = array(
                        "notify" => true,
                        "order_id" => $id,
                        "order_status_id" => $this->config->get("payment_maksuturva_cancelled"),
                        "comment" => $statuses[$id],
                    );
                    $this->model_sale_order->addOrderHistory($id, $data1);
                    break;

                // no news for buyer and seller
                case MaksuturvaGatewayImplementation::STATUS_QUERY_NOT_FOUND:
                case MaksuturvaGatewayImplementation::STATUS_QUERY_FAILED:
                case MaksuturvaGatewayImplementation::STATUS_QUERY_WAITING:
                case MaksuturvaGatewayImplementation::STATUS_QUERY_UNPAID:
                case MaksuturvaGatewayImplementation::STATUS_QUERY_UNPAID_DELIVERY:
                    $statuses[$id] = $this->language->get('NO_CHANGE') . ' (' . $response["pmtq_returntext"] . ')';
                    break;
            }
            //$statuses[$id]
        }
        $data['statuses'] = $statuses;
        $data['links'] = $links;
        $this->template = 'extension/payment/maksuturva_query';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        //$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
        $this->response->setOutput($this->load->view('extension/payment/maksuturva_query', $data));
    }

    private function getLink($route, $args, $ssl)
    {
        if ($this->url) {
            $url = $this->url->link($route, $args, $ssl);
        } else {
            $url = HTTPS_SERVER . "index.php?route=" . $route . "&" . $args;
        }
        return $url;
    }
}
?>
