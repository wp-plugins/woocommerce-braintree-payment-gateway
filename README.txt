=== WooCommerce Braintree Payment Gateway ===
Contributors: leljak
Donate link: http://www.leljak.com/buy-me-a-coffee/
Tags: WooCommerce, Braintree, payment gateway, credit card
Requires at least: 4.0
Tested up to: 4.1
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooCommerce Braintree Payment Gateway allows you to accept payments on your Woocommerce store.

== Description ==

WooCommerce Braintree Payment Gateway allows you to accept payments on your Woocommerce store. It authorizes credit card payments and processes them securely with your merchant account.

= Plugin Functionality: =

* Easy to install and configure
* Compatible with WordPress/Woocommerce plugins
* You don’t need any extra plugins or scripts to process the Transaction
* Accepts all major credit cards
* Automatic Payment Confirmation to update order transaction

Checkout Payment Form is responsive, adapts nicely to all mobile screen sizes.

This Plugin __does not__ support WooCommerce Subscriptions, but this functionality is in plan for the near future.

== Installation ==

= Automatic Installation =
* 	Login to your WordPress Admin area
* 	Go to "Plugins > Add New" from the left hand menu
* 	In the search box type "WooCommerce Braintree Payment Gateway"
*	From the search result you will see "WooCommerce Braintree Payment Gateway" click on "Install Now" to install the plugin
*	A popup window will ask you to confirm your wish to install the Plugin.

= Note: =
If this is the first time you've installed a WordPress Plugin, you may need to enter the FTP login credential information. If you've installed a Plugin before, it will still have the login information. This information is available through your web server host.

* Click "Proceed" to continue the installation. The resulting installation screen will list the installation as successful or note any problems during the install.
* If successful, click "Activate Plugin" to activate it, or "Return to Plugin Installer" for further actions.

= Manual Installation =
1. 	Download the plugin zip file
2. 	Login to your WordPress Admin. Click on "Plugins > Add New" from the left hand menu.
3.  Click on the "Upload" option, then click "Choose File" to select the zip file from your computer. Once selected, press "OK" and press the "Install Now" button.
4.  Activate the plugin.
5. 	Open the Settings page for WooCommerce and click the "Checkout" tab.
6. 	Click on the sub tab for "Braintree".
7.	Configure your "Braintree" settings. See below for details.

= Configure the plugin =
To configure the plugin, go to __WooCommerce > Settings__ from the left hand menu, then click "Checkout" from the top tab menu. You should see __"Braintree"__ as an option at the top of the screen. Click on it to configure the payment gateway.

* __Enable/Disable__ - check the box to enable Braintree Payment Gateway.
* __Title__ - allows you to determine what your customers will see this payment option as on the checkout page, default is "Credit card".
* __Description__ - controls the message that appears under the payment fields on the checkout page.
* __Sandbox__ - check the box to enable sandbox mode to test how would payments work for you, real payments will not be taken. Uncheck this option to put It in production mode.
* __Sandbox Merchant ID/Merchant ID__  	- enter your Braintree Merchant ID, this is gotten from your account page on [Braintree website](https://www.braintreepayments.com/).
* __Sandbox Public Key/Public Key__  	- enter your Braintree Public Key, this is gotten from your account page on [Braintree website](https://www.braintreepayments.com/).
* __Sandbox Private Key/Private Key__  	- enter your Braintree Private Key, this is gotten from your account page on [Braintree website](https://www.braintreepayments.com/).
* __Sandbox CSE Key/CSE Key__  			- enter your Braintree CSE Key, this is gotten from your account page on [Braintree website](https://www.braintreepayments.com/).
* Click on __Save Changes__ for the changes you made to be effected.

== Frequently Asked Questions ==

= What Do I Need To Use The Plugin =

1.	You need to have WooCommerce plugin installed and activated on your WordPress site.
2.	You need to open an account on [Braintree website](https://www.braintreepayments.com/).

= Does this Plugin support WooCommerce Subscriptions? =

No, It doesn't, but this functionality is in plan for the near future.

== Screenshots ==

1. Configuration page with options for the Plugin. 
2. Frontend shop Checkout page Payment Form. 

== Changelog ==

= 1.1.0 =
*   PHP version check changed from 5.3 to 5.2.1
*	Updated Braintree PHP Client Library to version 2.35.2. Released December 17, 2014.

= 1.0.0 =
*   First release

== Upgrade Notice ==