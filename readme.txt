=== USBSwiper Hosted Virtual Terminal ===
Tags: usbswiper, paypal, virtual terminal, vt, credit cards, credit card, payments, payment, visa, mastercard, american express, discover
Requires at least: 5.3
Tested up to: 6.0.1
Stable tag: 1.1.16
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Create PayPal transaction using swiper or manually and manage transactions.

== Description ==

Easily manage paypal transactions.

== Installation ==

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of USBSwiper, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type USBSwiper and click Search Plugins. Once you've found our plugin you can view details about it such as the the rating and description. Most importantly, of course, you can install it by simply clicking Install Now.

= Manual Installation =

1. Unzip the files and upload the folder into your plugins folder (/wp-content/plugins/) overwriting older versions if they exist
2. Activate the plugin in your WordPress admin area.

= Usage =

= Updating =

Automatic updates should work great for you.  As always, though, we recommend backing up your site prior to making any updates just to be sure nothing goes wrong.

== Screenshots ==

== Frequently Asked Questions ==

== Changelog ==

= 1.1.16 =
* Adds an InvoiceID prefix to avoid PayPal duplicate invoice errors (VT-43)

= 1.1.15 =
* Adds failed transactions to logs (VT-12)
* Adds ability to exclude merchants from being charged a platform_fee (VT-27)
* Adjusts brand_name and soft_descriptor to use merchant company name (VT-29)

= 1.1.14 =
* More adjustments to login/account redirects (VT-26)

= 1.1.13 =
* Adjusts redirects based on user account login / PayPal connection (VT-17)
* Adjusts Connect to PayPal button label (VT-18)

= 1.1.12 =
* Follow up to VT-14

= 1.1.11 =
* Adjusts redirect to go to /wp-login.php instead of /my-account (VT-14)

= 1.1.10 =
* Added - Transactions search with transaction id in backend

= 1.1.9 =
* Added - Connect to PayPal User Flow Change
* Added - Sent email for connect to PayPal
* Added - Sent email for disconnect to PayPal
* Added - Add Company name field and label changes

= 1.1.8 =
* Fix - Resolves order create and capture issue.
* Added - Handle fail order status
* Fix - removed (,) in price.
* Fix - Other small fixes related JS update to handle HostedFields

= 1.1.7 =
* Fix - Resolves an improperly formatted value, AF-38

= 1.1.6 =
* Fix - Onboarding failure handling.
* Feature - create and capture flow handling for the Capture method.
