=== Greyforest ::: WooCommerce Payment Gateway - Monero ===
Donate link: https://www.paypal.me/greyforestmedia
Tags: woocommerce, payment gateway, crypto currency, monero
Requires at least: 5.0
Tested up to: 5.5
Stable tag: 2.1.1
License: None
License URI: None

This plugin adds a minimal Monero payment gateway to a WooCommerce shop.

== Description ==

This plugin adds a minimal Monero payment gateway to a WooCommerce shop.

When customers check "Monero" on the Checkout page, they are redirected to the "Order Received" page, 
where they are presented a dynamically-generated QR code with the store's chosen wallet address, and a price in Monero
converted from current rates pulled from CoinGecko's API. The rates and QR code regenerate every minute, ensuring the price
is accurate.

To setup, enable the Gateway through WooCommerce's settings panel, enter your Monero wallet address, choose a fee/discount option if desired, 
and the percentage you would like to add/subtract to each payment as a fee/discount (enter 0 if none).

FUTURE UPGRADES:
Wallet address array to provide multiple possible addresses for randomization & potential enhanced privacy.


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress

To change payment icon, overwrite "GATEWAY-monero.png" in the plugin folder. 


== Changelog ==

= 2.1.1 =
* Updated price API to CoinGecko to continue free functionality
* Cleaned up jQuery code
* CSS tweaks
* Updated to not require separate rates.php file
* SVG icons added to folder for choice

= 2.0.1 =
* Fixed QR code text string getting null value
* Updated reduce_order_stock function to wc_reduce_stock_levels

= 2.0.0 =
* Rebuilt fee/discount option to dynamically calculate updated price as well as display/include a line item note in the emails and Checkout page
* Removed "message" option as it was throwing errors for the QR code address generation (and was unnecessary with order number hardcode in price)
* CSS tweaks to QR code section
* Added text string of wallet address for copying
* Removed "amount" + "orderid" + "memo" lines from QR code link (only symbol:address now)
* Changed crypto currency conversion totals to 7 decimal places for higher accuracy in price matching
* Added "000" + order ID number to end of crypto payment amount to hardcode order numbers into transactions permanently
* - Tested for perceptible value changes based on adding large order numbers (999999)
* - After 10 decimal places, the value does not add more than 0.01 USD

= 1.5 =
* Plugin update check served over HTTPS.
* Forced USD payment total to output to 2 decimal places.
* Added "USD" and "AMOUNT" to calculated payment sections on Rates page.

= 1.4 =
* Added element ID naming convention to rates page to prevent interfering styles.
* Rewrote rates page for dynamic functionality in scripts & output.
* Added more descriptive default "description" and "instructions" to plugin settings.

= 1.3 =
* Added "Settings" link on Plugins page.
* Added WooCommerce version check headers.

= 1.2 =
* Added ability to change message & add percentage-based fee.

= 1.1 =
* Addition of automatic updates API.
* Publicly served from Greyforest servers now.

= 1.0 =
* Created plugin.
