<?php

class ControllerExtensionPaymentBankTransfer extends Controller
{
    public function index()
    {
        $this->load->language('extension/payment/bank_transfer');

        $pending_amount = 0;
        if (isset($this->session->data['total_partial_payment_total_percent_dd'])) {
            $pending_amount = $this->session->data['total_partial_payment_total_percent_dd'];
        }
        if ($pending_amount > 0) {
            $data['bank'] = "Thank you for the purchase! <br/>
In case of layaway payment you’ll get the invoice that you should to pay in 3 working days by e-mail. The rest of the sum should be paid in 90 days since we receive the payment of the first invoice. The second bill will be sent after you pay the first one.<br/>
While you are in the process of payments the chosen item is reserved for you. The item will be sent after you complete your layaway payment and pay its total cost. ";
        } else $data['bank'] = nl2br($this->config->get('payment_bank_transfer_bank' . $this->config->get('config_language_id')));
        return $this->load->view('extension/payment/bank_transfer', $data);
    }

    public function confirm()
    {
        $json = array();

        if ($this->session->data['payment_method']['code'] == 'bank_transfer') {
            $this->load->language('extension/payment/bank_transfer');

            $this->load->model('checkout/order');

            $comment = $this->language->get('text_instruction') . "\n\n";
            $pending_amount = 0;
            if (isset($this->session->data['total_partial_payment_total_percent_dd'])) {
                $pending_amount = $this->session->data['total_partial_payment_total_percent_dd'];
            }
            if ($pending_amount > 0) {
                $comment .= "Thank you for the purchase! <br/>
In case of layaway payment you’ll get the invoice that you should to pay in 3 working days by e-mail. The rest of the sum should be paid in 90 days since we receive the payment of the first invoice. The second bill will be sent after you pay the first one.<br/>
While you are in the process of payments the chosen item is reserved for you. The item will be sent after you complete your layaway payment and pay its total cost. ";
            } else $comment .= $this->config->get('payment_bank_transfer_bank' . $this->config->get('config_language_id')) . "\n\n";

            $comment .= $this->language->get('text_payment');

            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_bank_transfer_order_status_id'), $comment, true);

            $json['redirect'] = $this->url->link('checkout/success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}