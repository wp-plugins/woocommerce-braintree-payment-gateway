<?php

/**
 * Plugin Name:       WooCommerce Braintree Payment Gateway
 * Plugin URI:        http://www.leljak.com/woocommerce-braintree-payment-gateway/
 * Description:       Braintree Payment Gateway allows you to accept payments on your Woocommerce store. It authorizes credit card payments and processes them securely with your merchant account.
 * Version:           1.1.0
 * Author:            Tomislav Leljak
 * Author URI:        http://www.leljak.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Github URI:		  https://github.com/leljak/WooCommerce-Braintree-Payment-Gateway/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Begins execution of the plugin.
 */
add_action( 'plugins_loaded', 'run_WC_braintree_payment_gateway' );

function run_WC_braintree_payment_gateway() {

	/**
	 * Tell WooCommerce that Braintree class exists 
	 */
	function add_WC_braintree_payment_gateway( $methods ) {
		$methods[] = 'WC_braintree_payment_gateway'; 
		return $methods;
	}
	add_filter( 'woocommerce_payment_gateways', 'add_WC_braintree_payment_gateway' );

	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

	/**
 	 * Braintree gateway class
 	 */
	class WC_braintree_payment_gateway extends WC_Payment_Gateway {

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->id               	= 'braintree';
			$this->icon             	= apply_filters( 'woocommerce_braintree_icon', plugins_url( 'public/images/cards.png' , __FILE__ ) );
			$this->has_fields       	= true;
			$this->method_title       	= 'Braintree';
			$this->method_description	= 'Braintree Payment Gateway authorizes credit card payments and processes them securely with your merchant account.';
			
			// Load the form fields
			$this->init_form_fields();

			// Load the settings
			$this->init_settings();

			// Get setting values
			$this->title       	= $this->get_option( 'title' );
			$this->description 	= $this->get_option( 'description' );
			$this->enabled     	= $this->get_option( 'enabled' );
			$this->sandbox     	= $this->get_option( 'sandbox' );
			$this->environment 	= $this->sandbox == 'no' ? 'production' : 'sandbox';
			$this->merchant_id 	= $this->sandbox == 'no' ? $this->get_option( 'merchant_id' ) : $this->get_option( 'sandbox_merchant_id' );
			$this->private_key 	= $this->sandbox == 'no' ? $this->get_option( 'private_key' ) : $this->get_option( 'sandbox_private_key' );
			$this->public_key  	= $this->sandbox == 'no' ? $this->get_option( 'public_key' ) : $this->get_option( 'sandbox_public_key' );
			$this->cse_key 		= $this->sandbox == 'no' ? $this->get_option( 'cse_key' ) : $this->get_option( 'sandbox_cse_key' );

			// Hooks
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
			add_action( 'admin_notices', array( $this, 'checks' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * Admin Panel Options
		 */
		public function admin_options() {
			?>
			<h3><?php _e( 'Braintree','woocommerce' ); ?></h3>
			<p><?php _e( 'Braintree Payment Gateway authorizes credit card payments and processes them securely with your merchant account.','woocommerce' ); ?></p>
			<table class="form-table">
				<?php $this->generate_settings_html(); ?>
				<script type="text/javascript">
					jQuery( '#woocommerce_braintree_sandbox' ).change( function () {
						var sandbox    = jQuery( '#woocommerce_braintree_sandbox_public_key, #woocommerce_braintree_sandbox_private_key, #woocommerce_braintree_sandbox_merchant_id, #woocommerce_braintree_sandbox_cse_key' ).closest( 'tr' ),
							production = jQuery( '#woocommerce_braintree_public_key, #woocommerce_braintree_private_key, #woocommerce_braintree_merchant_id, #woocommerce_braintree_cse_key' ).closest( 'tr' );

						if ( jQuery( this ).is( ':checked' ) ) {
							sandbox.show();
							production.hide();
						} else {
							sandbox.hide();
							production.show();
						}
					}).change();
				</script>
			</table> <?php
		}

		/**
		 * Check if SSL is enabled and notify the user
		 */
		public function checks() {
			if ( $this->enabled == 'no' ) {
				return;
			}

			// PHP Version
			if ( version_compare( phpversion(), '5.2.1', '<' ) ) {
				echo '<div class="error"><p>' . sprintf( __( 'Braintree Error: Braintree requires PHP 5.2.1 and above. You are using version %s.', 'woocommerce' ), phpversion() ) . '</p></div>';
			}

			// Show message if enabled and FORCE SSL is disabled and WordpressHTTPS plugin is not detected
			elseif ( 'no' == get_option( 'woocommerce_force_ssl_checkout' ) && ! class_exists( 'WordPressHTTPS' ) ) {
				echo '<div class="error"><p>' . sprintf( __( 'Braintree is enabled, but the <a href="%s">force SSL option</a> is disabled; your checkout may not be secure! Please enable SSL and ensure your server has a valid SSL certificate - Braintree will only work in sandbox mode.', 'woocommerce'), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) . '</p></div>';
			}
		}

		/**
		 * Check if this gateway is enabled
		 */
		public function is_available() {
			if ( 'yes' != $this->enabled ) {
				return false;
			}

			if ( ! is_ssl() && 'yes' != $this->sandbox ) {
				return false;
			}

			return true;
		}

		/**
	 	 * Initialise Gateway Settings Form Fields
	 	 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'       => __( 'Enable/Disable', 'woocommerce' ),
					'label'       => __( 'Enable Braintree Payment Gateway', 'woocommerce' ),
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no'
				),
				'title' => array(
					'title'       => __( 'Title', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					'default'     => __( 'Credit card', 'woocommerce' ),
					'desc_tip'    => true
				),
				'description' => array(
					'title'       => __( 'Description', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
					'default'     => 'Pay securely with your credit card.',
					'desc_tip'    => true
				),
				'sandbox' => array(
					'title'       => __( 'Sandbox', 'woocommerce' ),
					'label'       => __( 'Enable Sandbox Mode', 'woocommerce' ),
					'type'        => 'checkbox',
					'description' => __( 'Place the payment gateway in sandbox mode using sandbox API keys (real payments will not be taken).', 'woocommerce' ),
					'default'     => 'yes'
				),
				'sandbox_merchant_id' => array(
					'title'       => __( 'Sandbox Merchant ID', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Get your API keys from your Braintree account.', 'woocommerce' ),
					'default'     => '',
					'desc_tip'    => true
				),
				'sandbox_public_key' => array(
					'title'       => __( 'Sandbox Public Key', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Get your API keys from your Braintree account.', 'woocommerce' ),
					'default'     => '',
					'desc_tip'    => true
				),
				'sandbox_private_key' => array(
					'title'       => __( 'Sandbox Private Key', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Get your API keys from your Braintree account.', 'woocommerce' ),
					'default'     => '',
					'desc_tip'    => true
				),
				'sandbox_cse_key' => array(
					'title'       => __( 'Sandbox CSE Key', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Get your API keys from your Braintree account.', 'woocommerce' ),
					'default'     => '',
					'desc_tip'    => true
				),
				'merchant_id' => array(
					'title'       => __( 'Merchant ID', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Get your API keys from your Braintree account.', 'woocommerce' ),
					'default'     => '',
					'desc_tip'    => true
				),
				'public_key' => array(
					'title'       => __( 'Public Key', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Get your API keys from your Braintree account.', 'woocommerce' ),
					'default'     => '',
					'desc_tip'    => true
				),
				'private_key' => array(
					'title'       => __( 'Private Key', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Get your API keys from your Braintree account.', 'woocommerce' ),
					'default'     => '',
					'desc_tip'    => true
				),
				'cse_key' => array(
					'title'       => __( 'CSE Key', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Get your API keys from your Braintree account.', 'woocommerce' ),
					'default'     => '',
					'desc_tip'    => true
				),
			);
		}

		/**
	 	 * Initialise Credit Card Payment Form Fields
	 	 */
		public function payment_fields() {			
			?>
			<fieldset id="braintree-cc-form">
				<p class="form-row form-row-wide">
					<label for="braintree-card-number"><?php echo __( 'Card Number', 'woocommerce') ?> <span class="required">*</span></label>
					<input type="text" data-encrypted-name="braintree-card-number" placeholder="" autocomplete="off" maxlength="20" class="input-text wc-credit-card-form-card-number" id="braintree-card-number">
				</p>

				<p class="form-row form-row-first braintree-card-expiry">
					<label for="braintree-card-expiry-month"><?php echo __( 'Expiry', 'woocommerce') ?> <span class="required">*</span></label>
					<select name="braintree-card-expiry-month" id="braintree-card-expiry-month" class="input-text">
	                    <option value=""><?php _e( 'Month', 'woocommerce' ) ?></option>
	                    <option value='01'>01</option>
	                    <option value='02'>02</option>
	                    <option value='03'>03</option>
	                    <option value='04'>04</option>
	                    <option value='05'>05</option>
	                    <option value='06'>06</option>
	                    <option value='07'>07</option>
	                    <option value='08'>08</option>
	                    <option value='09'>09</option>
	                    <option value='10'>10</option>
	                    <option value='11'>11</option>
	                    <option value='12'>12</option>  
                	</select>

                	<select name="braintree-card-expiry-year" id="braintree-card-expiry-year" class="input-text">
                      	<option value=""><?php _e( 'Year', 'woocommerce' ) ?></option><?php
                      	for ($iYear = date('Y'); $iYear < date('Y') + 21; $iYear++) {
                            echo '<option value="' . $iYear . '">' . $iYear . '</option>';
                        } ?>
                    </select>
				</p>

				<p class="form-row form-row-last">
					<label for="braintree-card-cvc"><?php echo __( 'Card Code', 'woocommerce') ?> <span class="required">*</span></label>
					<input type="text" data-encrypted-name="braintree-card-cvc" placeholder="CVC" autocomplete="off" class="input-text wc-credit-card-form-card-cvc" id="braintree-card-cvc">
				</p>
			</fieldset>
			<?php
		}

		/**
		 * Outputs style used for Braintree Payment fields
		 * Outputs scripts used for Braintree Payment
		 */
		public function payment_scripts() {
			if ( ! is_checkout() || ! $this->is_available() ) {
				return;
			}

			wp_register_style( 'wc-braintree-style', plugins_url( 'public/css/woocommerce-braintree-payment-gateway-public.css' , __FILE__ ), array(), '20140918', 'all' );
    		wp_enqueue_style( 'wc-braintree-style' );

    		wp_enqueue_script( 'wc-braintree-js', 'https://js.braintreegateway.com/v1/braintree.js', array( 'jquery' ), WC_VERSION, true );
    		wp_enqueue_script( 'wc-braintree-payment-gateway', plugins_url( 'public/js/woocommerce-braintree-payment-gateway-public.js' , __FILE__ ), array( 'jquery' ), WC_VERSION, true );
			wp_localize_script( 'wc-braintree-payment-gateway', 'Braintree_params', array(
				'cse_key'           => $this->cse_key
			) );
		}

		/**
		 * Process the payment
		 */
		public function process_payment( $order_id ) {
			global $woocommerce;
			$order = new WC_Order( $order_id );

			require_once( 'includes/Braintree.php' );

			Braintree_Configuration::environment( $this->environment );
			Braintree_Configuration::merchantId( $this->merchant_id );
			Braintree_Configuration::publicKey( $this->public_key );
			Braintree_Configuration::privateKey( $this->private_key );

			$result = Braintree_Transaction::sale(array(
			    "amount" => $order->order_total,
			    "creditCard" => array(
			        "number" => $_POST["braintree-card-number"],
			        "cvv" => $_POST["braintree-card-cvc"],
			        "expirationMonth" => $_POST["braintree-card-expiry-month"],
			        "expirationYear" => $_POST["braintree-card-expiry-year"]
			    ),
			    "options" => array(
			        "submitForSettlement" => true
			    )
			));

			if ($result->success) {
			    echo("Success! Transaction ID: " . $result->transaction->id);
				// Payment complete
				$order->payment_complete( $result->transaction->id );
				// Add order note
				$order->add_order_note( sprintf( __( '%s payment approved! Trnsaction ID: %s', 'woocommerce' ), $this->title, $result->transaction->id ) );
				// Remove cart
				WC()->cart->empty_cart();
				// Return thank you page redirect
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order )
				);
			} else if ($result->transaction) {
				$order->add_order_note( sprintf( __( '%s payment declined.<br />Error: %s<br />Code: %s', 'woocommerce' ), $this->title, $result->message, $result->transaction->processorResponseCode ) );
			} else {
			    foreach ( ($result->errors->deepAll() ) as $error ) {
			        wc_add_notice( "Validation error - " . $error->message, 'error' );
			    }
			    return array(
					'result'   => 'fail',
					'redirect' => ''
				);
			}

		}

	}

}