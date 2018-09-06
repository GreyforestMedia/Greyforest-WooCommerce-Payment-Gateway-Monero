<?php
/*
Plugin Name: Greyforest ::: WooCommerce Payment Gateway - Monero
Plugin URI: https://www.greyforest.media/plugins
Description: Adds Monero (XMR) option to payment gateways.
Version: 2.0.0
Author: Greyforest Media
Author URI: https://www.greyforest.media
WC requires at least: 3.0.0
WC tested up to: 3.4
*/

///////////////////////////////////////////////////////////////////////////////
/// PLUGIN UPDATE CHECKER                                                   ///
///////////////////////////////////////////////////////////////////////////////
require 'plugin-update-checker/plugin-update-checker.php';
$MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://www.greyforest.media/plugins/wp-update-server/?action=get_metadata&slug=greyforest-woocommerce-payment-gateway-monero', //Metadata URL.
	__FILE__, //Full path to the main plugin file.
	'greyforest-woocommerce-payment-gateway-monero' //Plugin slug. Usually it's the same as the name of the directory.
);


///////////////////////////////////////////////////////////////////////////////
/// ADD "VIEW SETTINGS" LINK ON PLUGIN PAGE                                 ///
///////////////////////////////////////////////////////////////////////////////
function greyforest_woocommercepaymentgatewaymonero_settings_link($links) { 
  $settings_link = '<a aria-label="View Details" class="thickbox open-plugin-details-modal" href="plugin-install.php?tab=plugin-information&plugin=greyforest-woocommerce-payment-gateway-monero&TB_iframe=true&width=772&height=853">View Details</a>'; 
  $settings_link .= ' | <a aria-label="View Settings" href="admin.php?page=wc-settings&tab=checkout&section=monero">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'greyforest_woocommercepaymentgatewaymonero_settings_link' );



add_action('admin_enqueue_scripts', 'woocommerce_gateway_monero_custom_script');
function woocommerce_gateway_monero_custom_script($hook) {
	if( $hook != 'woocommerce_page_wc-settings' ) { return; }
	wp_enqueue_script( 'woocommerce_gateway_monero_gateway', plugin_dir_url(__FILE__) .'js/admin-monero-gateway-settings.js' );
}


///////////////////////////////////////////////////////////////////////////////
/// INIT PAYMENT GATEAY                                                     ///
///////////////////////////////////////////////////////////////////////////////
add_action('plugins_loaded', 'woocommerce_gateway_monero_init', 0);

function woocommerce_gateway_monero_init() {
	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

class WC_Gateway_monero extends WC_Payment_Gateway {

    /**
     * Constructor for the gateway.
     */
	public function __construct() {
		$this->id                 = 'monero';
		$this->icon               = plugins_url( 'GATEWAY-monero.png', __FILE__ );
		$this->has_fields         = true;
		$this->method_title       = __( 'Monero (XMR)', 'woocommerce' );
		$this->method_description = __( 'Allows Monero payments.', 'woocommerce' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions', $this->description );
		$this->address = $this->get_option( 'address');
		$this->feeordiscount_charge = $this->get_option( 'feeordiscount_charge');
		$this->feeordiscount_percentage = $this->get_option( 'feeordiscount_percentage');

		// Actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    	add_action( 'woocommerce_thankyou_monero', array( $this, 'thankyou_page' ) );

    	// Customer Emails
    	add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
    }


    /**
     * Initialise Gateway Settings Form Fields
     */
    public function init_form_fields() {

    	$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable '.$this->title.' Payment', 'woocommerce' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Title', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( $this->title, 'woocommerce' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
				'default'     => __( 'For payments made with '.$this->title.', a QR code will be generated with an address and current price in '.$this->title.'.', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'instructions' => array(
				'title'       => __( 'Instructions', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page and emails.', 'woocommerce' ),
				'default'     => 'To make a '.$this->title.' payment, please click or copy the URL from the QR code below. Payments must be made within 30 minutes or the order will be cancelled. Please include your order number in a separate description or notes field with your crypto payment.',
				'desc_tip'    => true,
			),
			'address' => array(
				'title'       => __( 'Wallet Address', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter the '.$this->title.' wallet address that you would like to use.', 'woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),			
			'feeordiscount_charge' => array(
				'title'       => __( 'Percentage-based Discount or Fee', 'woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Select if you wish to charge a percentage based fee or discount for this gateway.', 'woocommerce' ),
				'default'     => 'None',
				'desc_tip'    => true,
				'options'       => array(
					'None'          => __("None", "woocommerce"),
					'Discount'  => __("Discount", "woocommerce"),
					'Fee'  => __("Fee", "woocommerce"),
				),
			),		
			'feeordiscount_percentage' => array(
				'title'       => __( 'Percentage To Add/Subtract', 'woocommerce' ),
				'type'        => 'number',
				'description' => __( 'Enter a number from 0 - 100 to determine a percentage based fee or discount for every payment. DO NOT USE NEGATIVE NUMBERS! 1 = 1%, 10 = 10%, etc.', 'woocommerce' ),
				'default'     => '0',
				'desc_tip'    => true,
			),				
		);
    }
	

///////////////////////////////////////////////////////////////////////////////
/// ORDER RECEIVED PAGE OUTPUT                                              ///
///////////////////////////////////////////////////////////////////////////////
public function thankyou_page( $order_id ) {

/* WALLET ADDRESS  */ if ( $this->address ) { $payment_address = $this->address; }
/* FEE OR DISCOUNT */ if ( $this->feeordiscount_charge ) { $payment_feeordiscount_charge = $this->feeordiscount_charge; } else { $payment_feeordiscount_charge = "None"; }
/* PERCENTAGE      */ if ( $this->feeordiscount_percentage ) { $payment_discount = $this->feeordiscount_percentage; } else { $payment_discount = 0; }

/* ORDER NUMBER */ $payment_order = wc_get_order($order_id);
/* ORDER TOTAL  */ $payment_total = $payment_order->get_total();
/* ORDER ID     */ $payment_orderidforreceipt = $payment_order->get_id();
/* PAYMENT TYPE */ $payment_type = $payment_order->get_payment_method();

ob_start(); ?>

	
<table class="shop_table" style="margin-top:1em">
<tr>
<td colspan="2">
<?php if ( $this->instructions ) echo wpautop( wptexturize( $this->instructions ) ); ?>
</td>
</tr>

<tr>
<td colspan="2">
<div id="rates" style="width:100%;height:auto"></div>
</td>
</tr>
</table>

<script>
$(document).ready( function() {
$('#rates').load('<?php echo plugin_dir_url( __FILE__ ); ?>rates.php?payment_type=<?php echo $payment_type; ?>&payment_address=<?php echo $payment_address; ?>&payment_total=<?php echo $payment_total; ?>&payment_orderid=<?php echo $payment_orderidforreceipt; ?>');
});

setTimeout( function() {
$('#rates').load('<?php echo plugin_dir_url( __FILE__ ); ?>rates.php?payment_type=<?php echo $payment_type; ?>&payment_address=<?php echo $payment_address; ?>&payment_total=<?php echo $payment_total; ?>&payment_orderid=<?php echo $payment_orderidforreceipt; ?>');
}, 1000); 

setInterval( function() {
$('#rates').load('<?php echo plugin_dir_url( __FILE__ ); ?>rates.php?payment_type=<?php echo $payment_type; ?>&payment_address=<?php echo $payment_address; ?>&payment_total=<?php echo $payment_total; ?>&payment_orderid=<?php echo $payment_orderidforreceipt; ?>');
}, 60000); 
</script>

<div style="clear:both;">&nbsp;</div>
	
	
	
<?php
echo ob_get_clean();
}

    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
        if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() && $order->has_status( 'on-hold' ) ) {
			echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
		}
	}


    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		// Mark as on-hold (we're awaiting the payment)
		$order->update_status( 'on-hold', __( 'Awaiting Monero payment. ', 'woocommerce' ) );

		// Reduce stock levels
		$order->reduce_order_stock();

		// Remove cart
		WC()->cart->empty_cart();

		// Return thankyou redirect
		return array(
			'result' 	=> 'success',
			'redirect'	=> $this->get_return_url( $order )
		);
	}
}
}

	/**
 	* Add the Gateway to WooCommerce
 	**/
	function woocommerce_add_gateway_monero($methods) {
		$methods[] = 'WC_Gateway_monero';
		return $methods;
	}	

add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_monero' ); 





// BACS payement gateway description: Append custom select field
// add_filter( 'woocommerce_gateway_description', 'gateway_bacs_custom_fields', 20, 2 );
// function gateway_bacs_custom_fields( $description, $method_id ){
//     //
//     if( $method_id == 'monero' ){
//         ob_start(); // Start buffering
// 
//         echo '<div  class="monero-fields" style="padding:10px 0;">';
// 
//         woocommerce_form_field( 'field_slug', array(
//             'type'          => 'select',
//             'label'         => __("", "woocommerce"),
//             'class'         => array('form-row-wide'),
//             'required'      => false,
//             'options'       => array(
//                 ''          => __("Select something", "woocommerce"),
//                 'choice-1'  => __("Stellar", "woocommerce"),
//                 'choice-2'  => __("Ripple", "woocommerce"),
//             ),
//         ), "");
// 
//         echo '<div>';
// 
//         $description .= ob_get_clean(); // Append buffered content
//     }
//     return $description;
// }




////////////////////////////////////////////////
/////   WOO - CUSTOM CRYPTO FEE/DISCOUNT   /////
////////////////////////////////////////////////
function gf_monero_customfeeordiscount( $payment_fee ) {
	if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) || ! is_checkout() )
		return;
	
	$chosen_gateway = WC()->session->chosen_payment_method;
	
	// INIT NEW CLASS
	$myGateway = new WC_Gateway_monero();
	$new_feeordiscount_charge = $myGateway->get_option('feeordiscount_charge');	
	$new_feeordiscount_percentage = $myGateway->get_option('feeordiscount_percentage');	

	// PERCENTAGE CHECK + ROUNDING
	if ( (!empty($new_feeordiscount_percentage)) || ($new_feeordiscount_percentage > 0) ) {
		$payment_alteration = round(($new_feeordiscount_percentage / 100), 4);
	} else {
		$payment_alteration = 0;
	}
	
	// IF "DISCOUNT" SELECTED
	if ( $new_feeordiscount_charge == "Discount" ) {
		$payment_alteration = "-".$payment_alteration;
		$feeordiscount_text = $new_feeordiscount_percentage. "% Monero Discount";
	} 
	// IF "FEE" SELECTED	
	else if ( $new_feeordiscount_charge == "Fee" ) {
		$payment_alteration = $payment_alteration;
		$feeordiscount_text = $new_feeordiscount_percentage. "% Monero Fee";
	}
	// IF NEITHER ARE SELECTED	
	else { 
		$feeordiscount_text = "";
		$payment_alteration = 0;
	}
	
	// GENERATE AMOUNT
	$feeordiscount_final = (WC()->cart->cart_contents_total * $payment_alteration);
	
	// IF AMOUNT IS NOT ZERO, ADD TO CART
	if ( ( $chosen_gateway == 'monero' ) && ( $feeordiscount_final != "0" ) ) { WC()->cart->add_fee( $feeordiscount_text, $feeordiscount_final, false, '' ); }
}
add_action( 'woocommerce_cart_calculate_fees','gf_monero_customfeeordiscount', 25 );
 
 
 
 
// JQUERY AJAX TRIGGER UPDATING OF PAYMENT GATEWAYS SECTION
if ( ! function_exists( 'gf_crypto_cart_update_script' ) ) {
	function gf_crypto_cart_update_script() {
		if (is_checkout()) :
		?>
		<script>
			jQuery( function( $ ) {
				// woocommerce_params is required to continue, ensure the object exists
				if ( typeof woocommerce_params === 'undefined' ) { return false; }
	
				$('form.checkout' ).on( 'change', 'input[name="payment_method"]', function() {
						$(this).trigger( 'update' );
				});
			});
		</script>
		<style>tr.fee th {text-align:right !Important;} </style>
		<?php
		endif;
	}
add_action( 'wp_footer', 'gf_crypto_cart_update_script', 999 );
}

////////////////////////////////////////////////
/////   WOO - CUSTOM CRYPTO FEE/DISCOUNT   /////
////////////////////////////////////////////////
