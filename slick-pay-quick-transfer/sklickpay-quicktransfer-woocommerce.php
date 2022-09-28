<?php

class slickpay_QuickTransfer extends WC_Payment_Gateway
{
	public function __construct()
    {
		// global ID
		$this->id = "slickpay_quicktransfer";

		$title = __("Slick-Pay Quick Transfer", 'slickpay-quicktransfer');
		$description = __("Slick-Pay.com Quick Transfer Secured Payment Gateway", 'slickpay-quicktransfer');

		// Show Title
		$this->method_title = $title;

		// Show Description
		$this->method_description = $description;

		// Vertical tab title
		$this->title = $title;
		$this->description = $description;

		$this->icon = null;

		$this->has_fields = true;

		// Support default form with credit card
		$this->supports = array(
			'products',
			// 'default_credit_card_form'
		);

		// Setting defines
		$this->init_form_fields();

		// Load time variable setting
		$this->init_settings();

		// Turn these settings into variables we can use
		foreach ($this->settings as $setting_key => $value) {
			$this->$setting_key = $value;
		}

		// Plugin actions
		add_action('admin_notices', array($this, 'do_check_settings'));

		add_action('woocommerce_thankyou', array($this, 'do_complete_payment'));

		// Save settings
		if (is_admin()) {
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options'));
		}

	} // Here is the  End __construct()

	// Administration fields for specific Gateway
	public function init_form_fields()
    {
		$this->form_fields = array(
			'rib' => array(
				'title'		=> __( 'RIB', 'slickpay-quicktransfer' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Merchant bank account ID.', 'slickpay-quicktransfer' ),
				// 'default'	=> __( '00012345678912345678', 'slickpay-quicktransfer' ),
			),
			'fname' => array(
				'title'		=> __( 'First Name', 'slickpay-quicktransfer' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Merchant First Name of platform payment process.', 'slickpay-quicktransfer' ),
				// 'default'	=> __( 'Lorem', 'slickpay-quicktransfer' ),
			),
			'lname' => array(
				'title'		=> __( 'Last Name', 'slickpay-quicktransfer' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Merchant Last Name of platform payment process.', 'slickpay-quicktransfer' ),
				// 'default'	=> __( 'Ipsum', 'slickpay-quicktransfer' ),
			),
			'address' => array(
				'title'		=> __( 'Merchant address', 'slickpay-quicktransfer' ),
				'type'		=> 'textarea',
				'desc_tip'	=> __( 'Merchant physical address.', 'slickpay-quicktransfer' ),
				// 'default'	=> __( 'Address', 'slickpay-quicktransfer' ),
				'css'		=> 'max-width:450px;'
			)
		);
	}

	// Response handled for payment gateway
	public function process_payment($order_id)
    {
		global $woocommerce;

		$customer_order = new WC_Order($order_id);

		// This is where the fun stuff begins
		$payload = array(
			// Slick-Pay.com Quick Transfer API parameters
			"returnUrl" => $this->get_return_url($customer_order),
			"amount"    => $customer_order->order_total,
			"rib"       => $this->rib,
			"fname"     => $this->fname,
			"lname"     => $this->lname,
			"address"   => $this->address,
		);

        try {

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "http://slick-pay.com/api/slickapiv1/transfer");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);

            $result = curl_exec($ch);

			$result = json_decode($result, true);

            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($status < 200 || $status >= 300)
				throw new Exception(__("There is issue for connectin payment gateway. Sorry for the inconvenience.", 'slickpay-quicktransfer'));

            elseif (isset($result['errors']) && boolval($result['errors']) == true)
				throw new Exception($result['msg']);

        } catch (\Exception $e) {

			throw new Exception($e->getMessage());
        }

		$fh = fopen(plugin_dir_path(__FILE__) . 'redirect-' . $order_id . '.php', 'w+');
		fwrite($fh, '<?php header("Location: ' . $result['url'] . '"); exit;');
		fclose($fh);
		$redirect_url = plugin_dir_url(__FILE__) . 'redirect-' . $order_id . '.php';

		return array(
		  'result'   => 'success',
		  'redirect' => $redirect_url
		);
	}

	// Validate fields
	public function validate_fields()
    {
		return false;
	}

	// Payment gateway callback
	public function do_complete_payment($order_id)
	{
		global $woocommerce;

		$customer_order = new WC_Order($order_id);

		if (!$customer_order->is_paid()) {

			if (!empty($_GET['transfer_id'])) {

				try {

					$ch = curl_init();

					curl_setopt($ch, CURLOPT_URL, "http://slick-pay.com/api/slickapiv1/transfer/transferPaymentSatimCheck");
					curl_setopt($ch, CURLOPT_POSTFIELDS, [
						'rib'         => $this->rib,
						'transfer_id' => intval($_GET['transfer_id']),
					]);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
					curl_setopt($ch, CURLOPT_TIMEOUT, 20);

					$result = curl_exec($ch);

					$result = json_decode($result, true);

					$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

					curl_close($ch);

					if (!empty($result['orderId']) && ($status >= 200 || $status < 300)) {

						// Payment successful
						$customer_order->add_order_note(__("Slick-Pay.com payment completed.", 'slickpay-quicktransfer'));

						if ($redirect = realpath(plugin_dir_path(__FILE__) . 'redirect-' . $order_id . '.php'))
							@unlink($redirect);

						$customer_order->update_meta_data('slickpay_date', $result['date']);
						$customer_order->update_meta_data('slickpay_amount', $result['amount']);
						$customer_order->update_meta_data('slickpay_orderId', $result['orderId']);
						$customer_order->update_meta_data('slickpay_orderNumber', $result['orderNumber']);
						$customer_order->update_meta_data('slickpay_approvalCode', $result['approvalCode']);
						$customer_order->update_meta_data('slickpay_pdf', $result['pdf']);
						$customer_order->update_meta_data('slickpay_respCode', $result['respCode_desc']);

						// paid order marked
						$customer_order->payment_complete();
			
						// this is important part for empty cart
						$woocommerce->cart->empty_cart();
					} else {
						$customer_order->add_order_note(__("Slick-Pay.com payment status error !", 'slickpay-quicktransfer'));

						wc_clear_notices();
						wc_add_notice(__("An error has occured, please reload the page !", 'slickpay-quicktransfer'), 'error');
						wc_print_notices();
					}

				} catch (\Exception $e) {
					wc_clear_notices();
					wc_add_notice(__("An error has occured, please reload the page !", 'slickpay-quicktransfer'), 'error');
					wc_print_notices();
				}
			}
		}
	}

	public function do_check_settings()
    {
		if (
            empty($this->rib) ||
            empty($this->fname) ||
            empty($this->lname) ||
            empty($this->address)
        ) {

            print "<div class=\"error\"><p>". sprintf( __( "Please ensure that <a href=\"%s\"><strong>%s</strong></a> is configured." ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ), $this->method_title ) ."</p></div>";
		}
	}

}