<?php
/* Partial Payment Total for OpenCart v.3.0.x 
 *
 * @version 3.3.1
 * @date 05/09/2018
 * @author Kestutis Banisauskas
 * @Smartechas
 */
// Heading
$_['heading_title']	        = '<span style="font-weight: bold; color: green;">Partial Payment<br /></span>';
$_['heading_title_main']    = 'Partial Payment';


// Text
$_['text_extension']        = 'Order Totals';
$_['text_success']          = 'Success: You have modified Partial payment  total!';
$_['text_edit']             = 'Edit Partial Payment Total';
$_['text_percent_dd']       = 'Drop-down percentage for partial payment';
$_['text_price_percent']    = 'Percentage of partial payment by price';
$_['text_payment_pending']  = 'Payment pending';
$_['text_request_payment']  = 'Request Payment ';
$_['text_partial_week']     = 'Weekly';
$_['text_partial_month']    = 'Monthly';
$_['text_partial_year']     = 'Annually';
$_['text_partial_period']   = 'Partial payment Frequency  (Percent)';
$_['text_date_next_pay']    = 'Next payment';
$_['text_date_reminder']    = 'Next Auto reminder';
$_['text_subject']          = 'Payment Request ';
$_['text_order_id']         = 'Your Order';
$_['text_payment_request']  = 'Overdue Balance payment. Please click this link to pay the balance for this order. ';
$_['text_thank_you']        = 'Thank You  for buying <br /> <strong>%s</strong>';
$_['text_sent']             = 'Request to pay balance have been sent to: %s';
$_['text_sending']          = 'The request for payment of the balance  to the buyer is sending';
$_['error_email']           = 'Email Error';

// Entry
$_['entry_total']		    = 'Total';
$_['entry_status']          = 'Status';
$_['entry_sort_order']      = 'Sort Order';
$_['entry_geo_zone']	    = 'Geo Zone';
$_['entry_percent']         = 'Partial payment in percent by price';
$_['entry_period']          = 'Select drop-down frenquency for partial payment';
$_['entry_cron_period']     = 'Select frenquency for partial payment reminder';
$_['entry_weekly']          = 'Pay Weekly';
$_['entry_monthly']         = 'Pay Monthly';
$_['entry_annually']        = 'Pay Annually';
$_['entry_percent_dd']      = 'Partial payment in percent';
$_['entry_subject']         = 'Pending Payment Subject';
$_['entry_message']         = 'Pending Payment Message';
$_['entry_category']        = 'Allowed Categories';
$_['entry_product_ids']     = 'Excluded Product IDs';
$_['entry_customer_group']  = 'Allowed Customer Groups';
$_['entry_tax_class']       = 'Tax Class';
$_['entry_token']           = 'Secret Token';
$_['entry_cron_url']        = 'Cron Job URL.';



// Help
$_['help_percent']          = 'Enter Partial payment in percent: 100.00:15,150.00:25,300.00:45 (Cart_total:Partial_payment_percent,<br />Cart_total:Partial_payment_percent,<br />Cart_total:Partial_payment_percent...)';
$_['help_percent_dd']       = 'Enter Partial payment in percent: 10,25,30,50 (Partial_payment_percent,Partial_payment_percent,Partial_payment_percent...)';
$_['help_total']		    = 'The checkout total the order must reach before this payment method becomes active.';
$_['help_period']		    = 'Enter how frequently to customer will pay partial payment. In checkout, in payment method step, will appear drop-down.'; 
$_['help_total_sort']	    = 'Module sort order number must be greater than &quot;Total&quot;, &quot;Partial Payment Subtotal&quot; & &quot;Partial Payment Tax&quot; Sorting number.';
$_['help_category']         = 'Select for which categories the payment option will be available. Leave blank if no restriction.';
$_['help_product_ids']      = 'Add product IDs separated by comma(,) for which the method will not be available.';
$_['help_customer_group']   = 'The customer must be in these customer groups before this payment method becomes active. Leave blank if there is no restriction.';
$_['help_cron_period']      = 'Frenquency for partial payment reminder if customer not choose period on checkout';
$_['help_token']            = 'Enter a random token for security or use the one generated.';
$_['help_cron_url']         = 'Set a cron job on your server cPanel to call this URL. Set Cron job dayly.';


// Error
$_['error_permission']      = 'Warning: You do not have permission to modify partial payment  total!';