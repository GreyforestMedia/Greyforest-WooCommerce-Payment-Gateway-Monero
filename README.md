# Greyforest-WooCommerce-Payment-Gateway-Monero
An ultra-minimal Wordpress plugin to add a Monero (XMR) payment gateway to a WooCommerce shop.

## DESCRIPTION

When customers check "Monero" on the Checkout page, they are redirected to the "Order Received" page, where they are presented a dynamically-generated QR code with the store's chosen wallet address, and a price in Monero converted from current rates pulled from Coin Market Cap. The rates and QR code regenerate every minute, ensuring the price is accurate.

![CHECKOUT](/media/SCREENSHOT-qrcode.jpg)

## OPTIONS

To setup, enable the Gateway through WooCommerce's settings panel, then enter your Monero wallet address, choose a fee/discount option if desired, and the percentage you would like to add/subtract to each payment as a fee/discount (enter 0 if none).

* **Wallet Address:** Monero wallet address to receive funds
* **Percentage-Based Discount or Fee:** Option to add/subtract a fee/discount for using this gateway
* **Percentage To Add/Subtract:** Number used to determine percentage of fee/discount

![SETTINGS](/media/SCREENSHOT-settings.jpg)

## DISCOUNT/FEE

Discount/fees are updated dynamically on the checkout page and added as a line item to the order for record keeping.

![CHECKOUT](/media/SCREENSHOT-checkout.jpg)

## DONATE
XMR: 82hwVhCKzat9NW9UCMsn3fAeAEAccsMC3FRpHu9jAeNvWYKhTsJr622a2s7vZyt3XfCTkG69SeZuWMpuwAcLM3qgCoXfGRa
