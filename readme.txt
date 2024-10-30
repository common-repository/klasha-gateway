=== Klasha Payment Gateway for WooCommerce ===

Contributors:      Klasha
Plugin Name:       Klasha Payment Gateway for WooCommerce
Plugin URI:        https://www.klasha.com
Tags:              payment, checkout, woocommerce, klasha, gateway, payment gateway
Author URI:        https://www.klasha.com
Author:            Klasha
Requires at least: 5.8 or higher
Tested up to:      6.2
Stable tag:        2.0.8
Version:           2.0.8
Requires PHP:      7.2 or higher
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
This is a Klasha payment gateway for WooCommerce.

The WooCommerce Klasha payment gateway plugin allows you to accept payment on your WooCommerce store through multiple payment channels via Klasha. You can signup for a Klasha merchant account <a href="https://dashboard.klasha.com/signup">here</a>.

The following are the payment methods you will be able to accept in your WooCommerce store with this WooCommerce Klasha payment gateway plugin:

* __Card Payments__
* __M-Pesa__
* __Mobile Money__
* __Klasha Wallet__
* __Bank Transfer__
* __USSD__

= PLUGIN FEATURES =
* __Multiple payment channels__ available for your customers.
* __Seamless integration__ into the WooCommerce checkout page.

= NOTE =
* Your WooCommerce store country can be in any country.
* Currencies supported by Klasha: NGN, KES, ZMW, ZAR, USD, UGX (updated regularly).

= SUGGESTIONS/FEATURE REQUEST =

If you have suggestions or a new feature request, feel free to get in touch with klasha via support@klasha.com.

You can also follow klasha on twitter <a href="https://twitter.com/Klasha_Inc">@Klasha_Inc</a> and instagram <a href="https://www.instagram.com/klasha_inc/">@klasha_inc</a>.

== Installation ==

= AUTOMATIC INSTALLATION =

* Login to your WordPress Admin area.
* Go to “Plugins > Add New” from the left hand menu.
* In the search box type “WooCommerce Klasha Payment Gateway”.
* From the search result you will see “WooCommerce Klasha Payment Gateway” click on “Install Now” to install the plugin.
* A popup window will ask you to confirm your wish to install the Plugin.
* Click “Proceed” to continue the installation. The resulting installation screen will list the installation as successful or note any problems during the install.
* If successful, click “Activate Plugin” to activate it.
* Open the settings page for WooCommerce and click the “Payment Gateways,” tab.
* Click on the sub tab for “Klasha Gateway”.
* Configure your “Klasha Gateway” settings. See below for details.

= MANUAL INSTALLATION =

* Download the plugin zip file.
* Login to your WordPress Admin. 
* Click on “Plugins > Add New” from the left hand menu.
* Click on the “Upload” option, then click “Choose File” to select the zip file from your computer. Once selected, press “OK” and press the “Install Now” button.
* Activate the plugin.
* Open the settings page for WooCommerce and click the “Payment Gateways,” tab.
* Click on the sub tab for “Klasha Gateway”.
* Configure your “Klasha Gateway” settings. See below for details.

= CONFIGURE THE PLUGIN =

To configure the plugin, go to WooCommerce > Settings from the left hand menu, then click “Payment Gateways” from the top tab. You should see “Klasha Payment Gateway” as an option at the top of the screen. Click on it to configure the payment gateway.

* Enable/Disable – Check the box to enable Klasha Payment Gateway.
* Title – Allows you to set the payment method title that your customers will see this payment option as on the checkout page.
* Description – Controls the message that is shown under the Klasha payment method on the checkout page. Here you can list the types of cards you accept.
* Test Mode – Check this to enable test mode, remember to uncheck this if you are ready to accepting live payment on your site.
* Public Key – Enter your public key here.
* Secret Key – Enter your private key here.
* Click on Save Changes for the changes you made to be effected.

= SERVICES USED =

Some services are hosted independently on our server to facilitate payment on our platform.

* countries.json: This file is used to fetch country codes when country names are provided.
* pay.js: This inline JavaScript code is utilized to create a popup for our payment gateway.

== Screenshots ==
[https://i.imgur.com/13UlhBN.png  Klasha payment gateway WooCommerce settings page]
[https://i.imgur.com/wC0krpw.png  Klasha payment popup on the website - MPesa]
[https://i.imgur.com/SnRq6k3.png  Klasha payment popup on the website - Card payment]

== Changelog ==


= VERSION 1.0.0 =
* First release

= VERSION 1.0.1 =
* Fix: Plugin conflict

= VERSION 2.0.0 =
* New: Add support for Ghanian Cedis (GHS)
* New: Add support for Kenyan Shillings (KSH)

= VERSION 2.0.1 =
* New: M-Pesa payment method
* New: Mobile money payment method

= VERSION 2.0.2 =
* New: Klasha wallet payment method
* New: Bank Transfer payment method
* Bug fixes: State and country validation

= VERSION 2.0.3 =
* Bug fixes: Currency validation

= VERSION 2.0.4 =
* Bug fixes: Currency validation

= VERSION 2.0.5 =
* Bug fixes: Versioning

= VERSION 2.0.6 =
* Update: Updated to work with latest version of Wordpress

= VERSION 2.0.7 =
* Update: Test environment fixes

= VERSION 2.0.8 =
* Update: Version update

== Frequently Asked Questions ==

= What Do I Need To Use The Plugin =

* You need to have the WooCommerce plugin installed and activated on your WordPress site.
* You need to open a merchant account on <a href="https://dashboard.klasha.com/signup">Klasha</a>.