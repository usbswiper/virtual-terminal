=== USBSwiper Hosted Virtual Terminal ===
Tags: usbswiper, paypal, virtual terminal, vt, credit cards, credit card, payments, payment, visa, mastercard, american express, discover
Requires at least: 5.3
Tested up to: 6.5.4
Stable tag: 3.2.2
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

= 3.2.2 =
* Fix - Tax calculation with shipping and handling amount. ([VT-108](https://github.com/usbswiper/virtual-terminal/pull/68))
* Update - Adjust billing and shipping address logic. ([VT-99](https://github.com/usbswiper/virtual-terminal/pull/69))

= 3.2.1 =
* Update - Adjust billing and shipping address logic. ([VT-99](https://github.com/usbswiper/virtual-terminal/pull/67))
* Fix - Zettle transaction environment Column is Empty in admin side. ([VT-105](https://github.com/usbswiper/virtual-terminal/pull/64))
* Update - Shipping/Billing Address Styling in admin and transaction page. ([VT-107](https://github.com/usbswiper/virtual-terminal/pull/67))
* Update - Include handling amount in tax calculation. ([VT-108](https://github.com/usbswiper/virtual-terminal/pull/65))

= 3.2.0 =
* Feature - Added Product level tax enable and disable setting.
* Feature - Added Processor Response Code on transaction.
* Feature - Adjust Print Receipt button and print styling.
* Update - Adjust billing and shipping address logic.
* Update - Validation form adjustments on Zettle payment.

= 3.1.0 =
* Feature - Added void button on authorize transactions.
* Update - PayPal/Zettle api with latest version.
* Compatibility with latest WordPress version 6.5.4

= 3.0.3 =
* Fix - Brand Name not saving the updated value when merchants try to update.
* Fix - Dashboard link is broken when trying to use it from the user details page.

= 3.0.2 =
* Feature - Added credit card AVS and CVV2 result Codes on the transaction details page.
* Fix - Display only merchant transaction instead of all accounts in transactions search.

= 3.0.1 =
* Feature - Added Zettle transactions supported currency notice.

= 3.0.0 =
* Feature - Added Zettle Integration.

= 2.3.4 =
* Feature - Added option for merchant to set time zone in the profile / dashboard.
* Feature - Merchant orders and logs reflect based on set time zone accordingly.
* Feature - Added a Terms and Conditions checkbox to the verification page.

= 2.3.3 =
* Fix - Brand name missing in soft_descriptor field.

= 2.3.2 =
* Enhancement - VT Form tax field and product styling.

= 2.3.1 =
* Fix - Platform fee for capture in Invoice.

= 2.3.0 =
* Feature - Added a setting for upload brand login in MyAccount >> Account details page.
* Feature - Branding details Added in email notifications and invoice
* Feature - Added sorting and search filter for transactions and invoices
* Feature - Added Discount Feature in VT form. In VT form Discount will apply using percentage or flat rate
* Feature - Added setting for Tax Rules. So, merchant will easily add multiple tax rates/rules in the their account
* Feature - Added setting for default tax rule
* Feature - Added new tax rule option to include shipping
* Feature - Search tax rule in VT form and apply correct rule
* Enhancement - VT Form fields design changes
* Enhancement - VT Form Product field design
* Fix - Correct merchant brand name in email notifications for invoice and transactions
* Fix - Only positive numbers are allowed for QTY and QTY Input sanitization

= 2.2.4 =
* Fix - Transactions post not show up front-end public search. ([VT-69](https://github.com/usbswiper/virtual-terminal/pull/40))

= 2.2.3 =
* Fix - Pagination in product listing screen in my-account page. ([VT-70](https://github.com/usbswiper/virtual-terminal/pull/39))

= 2.2.2 =
* Fix - Manage declined status on transactions filter.

= 2.2.1 =
* Fix - Adjusts version tags.

= 2.2.0 =
* Feature - Session timeout message and automatic logout if no response is given.
* Tweak - If Invoice mode is enabled, billing email is required.  If VT mode is enabled, billing email is optional.
* Fix - Filters and search in admin -> Transactions.
* Fix - Allows products to be less than 1.00 (but greater than 0.00)
* Fix - Net value is read-only and calculated based on the line item details.
* Fix - Net value calculates correctly when line items are removed.

= 2.1.0 =
* Fix - Fixes in the user session management
* Fix - Fixed Product min value is greater than or equal to 1
* Enhancement - VT Form validation and design Fixes
* Enhancement - Enhanced Search filters for the Admin

= 2.0.2 =
* Fix - Resolve PFW PayPal SDK conflict with USBSwiper PayPal SDK

= 2.0.1 =
* Fix - Verification flow for existing merchant

= 2.0.0 =
* Feature - Option to enable/disable email notices to the merchant ([VT-48](https://github.com/usbswiper/virtual-terminal/pull/21))
* Feature - Added button for resend email receipt ([VT-44](https://github.com/usbswiper/virtual-terminal/pull/22))
* Feature - Added verification form for merchant verification before onboarding ([VT-47](https://github.com/usbswiper/virtual-terminal/pull/23))
* Update - Update virtual terminal page UI interface ([VT-35](https://github.com/angelleye/paypal-woocommerce/pull/1800))
* Feature - Added new product interface in my-account page ([VT-34](https://github.com/usbswiper/virtual-terminal/pull/24))
* Feature - Select multiple product in virtual terminal form ([VT-34](https://github.com/usbswiper/virtual-terminal/pull/25))
* Feature - Merchant able to create invoice using virtual terminal form ([VT-32](https://github.com/usbswiper/virtual-terminal/pull/26))
* Feature - Added capability to pay via PayPal, Venmo and PayLater via Invoice payment ([VT-32](https://github.com/usbswiper/virtual-terminal/pull/26))
* Enhancement - Refund and capture feature with popup ([VT-issues-fixes](https://github.com/usbswiper/virtual-terminal/pull/29))
* Fix - Transaction filter not working with user. ([VT-50](https://github.com/usbswiper/virtual-terminal/pull/27))

= 1.1.17 =
* Removes public access from Transactions log (VT-45)

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
