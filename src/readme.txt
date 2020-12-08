=== Bitcoin Payment Gateway for WooCommerce ===
Contributors: tripleatechnology, BrianHenryIE
Donate link: https://triple-a.io/
Tags: bitcoin woocommerce,bitcoin payments,bitcoin,crypto payment gateway,bitcoin wordpress plugin
Requires at least: 4.0
Tested up to: 5.6
Stable tag: 1.5.0
Requires PHP: 5.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Start accepting Bitcoins now on your website and diversify your revenue right away. Powered by TripleA.

== Description ==

Accept Bitcoin now on your website and diversify your revenue right away. Powered by TripleA.

Get up and running in under a minute with our easy and simple-to-use plugin. Enjoy an easy setup, minimal or no learning curve and all the benefits bitcoin payments bring you and your customers.

= TripleA for WooCommerce =

This plugin equips online businesses using WordPress WooCommerce with the ability to accept and process bitcoin payments seamlessly.

* No chargebacks or fraud.
* Dedicated support.
* No registration or login needed.
* No setup cost or recurring fees.
* No commissions or platform fees on bitcoin payments.
* Non custodial system: wallet to wallet, no intermediary.
* Email notification for every transaction.

In addition, we offer bitcoin to local currency settlement too:

* Easily settle payments to your bank account in your local currency.
* Accept bitcoin payments without needing to understand or handle bitcoin.
* No impact from volatility, receive the expected amount.
* A 0.8% fee for withdrawals is the only cost.
* Real-time exchange rates for bitcoin-to-local-currency conversion.

= Settlement options =

We offer 2 easy options for more flexibility.

* __Bitcoin (Free)__: Direct bitcoin payments without any fee. From their wallet to yours, total privacy, without going through TripleA.
* __Local currency (withdrawal fee)__: No volatility. Easily withdraw to your bank account. Single 0.8% commission.


= Supported currencies =

•	United States dollar (USD)
•	Singapore dollar (SGD)
•	Euro (EUR)
•	Malaysian Ringgit (MYR)
•	Philippine peso (PHP)
•	Indonesian Rupiah (IDR)
•	Thai Baht (THB)
•	Vietnamese dong (VND)
•	South Korean won (KRW)
•	New Taiwan Dollar (TWD)
•	Hong Kong Dollar (HKD)
•	Pound sterling (GBP)
•	Japanese yen (JPY)
•	Australian dollar (AUD)
•	New Zealand dollar (NZD)
•	Canadian dollar (CAD)
•	Swiss franc (CHF)
•	Norwegian krone (NOK)
•	Swedish krona (SEK)



== Installation ==

1. Install via the searchable Plugin Directory within your WordPress site's plugin page.
1. (Or: Upload `triplea-cryptocurrency-payment-gateway-for-woocommerce.php` to the `/wp-content/plugins/` directory.)
1. Activate the plugin through the 'Plugins' menu in WordPress.

= Choose your preferred settlement mode =

We give you the option:

- **Receive bitcoin**: If you are familiar with bitcoin and would like to receive bitcoin payments into your own bitcoin wallet
- **Receive local currency**: If your prefer to avoid a learning curve, avoid accounting trouble or simply prefer to be paid local currency to your bank account.

= To receive bitcoin =

1. Provide the master public key of your Bitcoin wallet.
1. Provide an email address to receive payment notifications.
1. Click 'Activate'. Your wallet will be linked to a TripleA account.
1. Settings will be saved, page will reload automatically and you will be good to go!

**Important note**: Our plugin generates a new, unique bitcoin payment address (thanks to your public key).
Our plugin does not support displaying the same address each time, so please do not enter a bitcoin address in the form.

We recommend you create a new Bitcoin wallet using your preferred wallet software or app (to avoid mixing notifications between TripleA and separate/previous usage).

= Using a TripleA local currency wallet: =

1. Provide an email account to associate with your TripleA Local Currency account.
1. We require One-Time Password validation of your email address (code sent via email).
1. Click 'Activate'. Your TripleA local currency account will be created.
1. Settings will be saved, page will reload automatically and you will be good to go!

= Customise look & feel =

We like to keep things short, clear, and simple.
If you require more than customising the payment gateway text and logo, let us know at <a href="mailto:support@triple-a.io">support@triple-a.io</a>.

Certain WooCommerce plugins might add custom order statuses. We have tried to accommodate this, however carefully test a payment if you change the default settings and let us know if you're uncertain about anything.

== Frequently Asked Questions ==

= Can customer pay with bitcoins without registering on my website? =

There is no account needed for your clients to pay with bitcoins. They just scan the payment QR code and enter the right amount to pay. Very Easy.

= Which cryptocurrency wallet do you support? =

We support all wallets allowing public keys, meaning BIP 44-compatible HD wallets.

= Can you help me to integrate bitcoin payments to my website? =

Of course, our support team is always here to help. <a href="mailto:support@triple-a.io">Contact us by e-mail</a>.


== Screenshots ==

1. Seamless integration with WooCommerce checkout - Bitcoin Payment Gateway by TripleA
2. Appeal and cater to an international audience - Bitcoin Payment Gateway by TripleA
3. Receive payments in Bitcoin or cash - Bitcoin Payment Gateway by TripleA
4. Receive bitcoin payments into your own bitcoin wallet - Bitcoin Payment Gateway by TripleA
5. Receive bitcoin payments into your own bitcoin wallet - Bitcoin Payment Gateway by TripleA
6. Receive bitcoin payments as local currency in your bank account - Bitcoin Payment Gateway by TripleA
7. Receive bitcoin payments as local currency in your bank account - Bitcoin Payment Gateway by TripleA


== Changelog ==

= 1.4.3 =
Minor bug fixes and plugin file structure improvements.

= 1.4.0 =
Payment form expiry now at 25 minutes instead of 15.
Minor QR code related bug fix.
Plugin stability and performance improvements, thanks to open-source contributors.

= 1.3.1 =
Added configuration options for bitcoin payment option in checkout page.
Added order status customisation (only for those who know exactly what they're doing!).
Added debug log settings (enable/disable logging, easily view log, easily clear log).

= 1.2.1 =
Confirmed working with latest WooCommerce v4.0.1 and latest Wordpress (v5.3.2)

= 1.2.0 =
Overhauled plugin settings page, to make things simpler, clearer and hopefully much less confusing for some users.

= 1.1.3 =
Fixed T&C not appearing on some sites with our plugin enabled.

= 1.1.2 =
QR code is now a link. Click or tap to open with default bitcoin wallet (should work on mobile, depends on mobile setup and app used).
Minor improvement added for sites with custom checkout submit buttons.


== Upgrade Notice ==

= 1.5.0 =
Upgraded the plugin to use a new API by TripleA.
Instant confirmation available when using local currency settlement.
Better checkout page integration, less UI/CSS bugs thanks to iframe loading, and more.
Better account management (sandbox payments available; better email notifications; integration credentials provided..).

= 1.4.8 =
Small change in debug info display.

= 1.4.7 =
Qr code not updated when user paid too little.

= 1.4.6 =
CSS styling improvement to avoid interference with qr code size on some sites.

= 1.4.5 =
Bug fix for users experiencing problems updating product images while other plugins (such as Tera Wallet) are also enabled.

= 1.4.3 =
No need to update unless the "place order/pay with bitcoin" button on your checkout page is misbehaving.

= 1.4.0 =
Please update this plugin, to ensure the best experience for yourself and your customers.
Plugin stability and performance improved. Minor bugfixes included.
Simply let WordPress update the plugin for you, no further action required.

= 1.3.1 =
Apologies for the required bugfix for the encryption system used.
Please update this plugin, to ensure you no users experience problems with placing orders.

= 1.3.0 =
Please update this plugin, to ensure you benefit from the latest improvements.
After updating, no further action is needed but it is recommended that you have a look at the improved settings page and save your preferences.


== About TripleA ==

Triple A is headquartered in Singapore and in the process of becoming the first bitcoin payment gateway licensed by the Monetary Authority of Singapore (MAS).
Find out more about us at [Triple-A.io](https://triple-a.io/ "TripleA Payment Gateway website")

