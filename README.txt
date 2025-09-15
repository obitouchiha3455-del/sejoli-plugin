=== Plugin Name ===
Contributors: ridwanarifandi
Donate link: https://ridwan-arifandi.com
Tags: membership, affiliate, content, checkout
Requires at least: 6.0.0
Tested up to: 6.1.1.
Stable tag: 1.12.0

Premium membership, affiliate and reseller plugin

== Description ==

Coming soon

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `sejoli.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Input your license key with sejoli member access data

== Frequently Asked Questions ==

Coming soon

== Screenshots ==

Coming soon

== Changelog ==

1.14.7 06 Agustus 2025

* Fix - Bug Hit RajaOngkir V2
* Fix - Bug District Info in Profile Page
* Fix - Bug District Info in Email & WA Notif

1.14.6 24 Juli 2025

* Fix - Bug Calculate Ongkir RajaOngkir V2 Integration

1.14.5 21 Juli 2025

* Fix - RajaOngkir V2 Integration
* Fix - Bug Invoice Email

1.14.4 17 Juni 2025

* Fix - Feature PPN by Product Settings
* Fix - Information PPN in Notif Email & WhatsApp
* Fix - wpadmin bar colors
* Fix - button help widget


1.14.3 16 Mei 2025

* Feat - Add show/hide use of sejoli widget based on user preferencing
* Feat - Show quantity, biaya transaksi, ppn data in whatsapp notification
* Feat - Add {{renew*url}} shortcode in bulk notification
* Feat - Show shortcode list in bulk notification
* Feat - Add quantity & payment method in export orders
* Feat - Add feature update subscription status in admin
* Feat - Add feature to show/hide password input in login & register form
* Feat - Add feature to show/hide password input in all checkout form
* Fix - menu walker member menu
* Fix - updater error if server severaly down
* Fix - bug calculate grand total if using coupon free shipping
* Fix - bug calculate grand total reduce with shipping cost
* Fix - bug calculate free shipping
* Fix - wpadmin bar background colour
* Fix - shipment template email typo text
* Fix - bug reminder post meta field settings
* Fix - number type supported decimal in price product setting field
* Fix - grand total include variants
* Fix - some several bug style checkout page new design
* Fix - bug update checker by subscription status
* Fix - typo in pengaturan kupon bagian peraturan
* Fix - bug filter affiliate commission in member area
* Fix - translasi support
* Fix - some several bug style checkout page new design (compact, smart, modern) (mobile)
* Fix - copy in thankyou page for safari iphone/ipad/mac
* Fix - countdown broken safari iphone/ipad/mac
* Fix - compatibility with php 8.3

1.14.2 08 Januari 2025

* Feat - show/hide detail order in checkout page
* Fix - takeout woowandroid
* Fix - bug sidebar menu member area
* Fix - bug checkout with duitku in checkout script
* Fix - bug bulk notification data email content
* Fix - translasi support


1.14.1 14 Desember 2024

* Fix - fix compatibility with WP v6.7.1
* Fix - bug error text domain wp v6.7.1
* Fix - checkout new design style broken with semantic ui
* Fix - fix scrolling checkout script on iframe


1.14.0 11 Desember 2024

* Feat - New Design Checkout Page (Modern, Smart, Compact, Less is More) Style Options
* Feat - Checkout Script, for using checkout from any where (another website/landing pages)
* Feat - Adding Payment Channel Bank Mega Syariah in Moota
* Feat - Change Label J&T Express to JNT Express for shipping
* Fix  - Change Bulk Notification field from textarea to rich text


1.13.15 15 November 2024

* feat show/hide contact detail in pohon jaringan
* feat - support international phone number
* fix bug follow up link, validasi phone number length, validasi password, update endpoint woowa api
* fix bug coupon only free ongkir & bug weight calculating shipment rajaongkir
* fix duplicate user group field in profile page
* compatibility support with WordPress Latest Version v6.7


1.13.14 2 September 2024

* feat add bsi bank to duitku and jago to moota
* fix bug association not editing in coupon
* fix bug fb pixel in redirect and thank you page & bug error notice
* fix bug bump sale with discount fixed value
* fix bug update status when commission is nulled
* fix bug shipment cost not included with total
* fix bug modal popup order detail error, while order without affiliate
* fix bug when page template not detected in access page & update composer
* fix bug in wordpress version > 6


1.13.13 11 Juli 2024

* fix bug & improve security threshold recaptcha in checkout & register page
* fix bug error js recaptcha in page thank you and confirm payment
* fix bug user role editor/author not accessed wp*admin
* fix bug tier affiliate when complete order
* fix bug wallet support for applied coupon using wallet, change woowa api endpoint
* fix bug bump sale discount calculating
* fix bug phone validation on register & update profile 
* fix bug fb pixel Affiliate ID always appears even though there is no affiliate
* fix bug access association field not showing on update access post
* fix bug duitku fee calculating for e-wallet (ovo, shopee, qris, etc)

1.13.12 06 April 2024

* feat add labeling 'renewal coupon' for affiliate coupon
* fix bug applied coupon for renewal order with renewal coupon (Issue: The grand total calculation at checkout is different when applying a coupon)
* fix create child affiliate coupons for order renewal coupons
* fix menu access issues not being accessed even though the order has been completed
* fix validation coupon when limit date expired
* fix error data license in admin and member area, heavy data record issue

1.13.11 28 Maret 2024

* fix bug coupon validation when submit checkout

1.13.10 27 Maret 2024

* fix bug coupon calculate with initial cost
* fix bug coupon validation for renewal coupon

1.13.9 25 Maret 2024

* feat add field setting currencies likely woocommerce
* feat add coupon for renewal order only
* feat error handler message in ajax process
* fix issue auto generate password on register
* fix issue show error message from ajax checkout
* fix empty field after submit register form
* fix change text notif registration
* fix update service code name rajaongkir
* fix new issue of reminder still sending notif after renewall
* fix bug association affiliate in renewall order
* fix issue update resi when change status by detail order
* fix bug trigger event FB & Tiktok on event view checkout page duplicating data
* fix bug in register
* fix bug file sejoli log permission
* fix bug status license in member area
* fix WA support international number
* fix issue invoice asal 0 show in detail order
* fix generate recieptient email using shortcode {{affiliate-email}}

1.13.8 05 Februari 2024

* feat add shortcode courier name in notification
* feat add shortcode number resi in notification
* fix issue checkout with donation
* fix issue lisensi one payment time
* fix issue layout template email notif product variation
* fix issue error notice in file log admin chmod
* fix issue layout notifikasi WhatsApp (data shipment & address)
* fix bug error fb & tiktok pixel in checkout page
* fix issue error in delete data log
* fix issue product in link affiliate, when product affiliate is not activated, but product showing in affiliate link
* fix detail variation in checkout detail order and thank you page
* fix Issue alter table reminder data

1.13.7 22 Januari 2024

* fix display login info in renewall checkout page
* fix issue Limit Coupon Usage
* fix issue Renewall Subscription but License not Re-Activate
* fix issue FB Conversion API
* fix issue Produk dinotifikasi masal tidak keluar semua
* fix issue Show Product Affiliate only in Generate Affiliate Link
* fix move member area menu icon position from right after text to left
* fix update logo shopeepay qris ada logo qrisnya
* fix redaksi teks dihalaman checkout
* fix redaksi Moota bank labeling
* fix layout form checkout jika opsi kupon dan login dinonaktifkan layout jd gk sesuai
* fix CSV Data Order/Sales
* fix urutan Event FB Conversion API & Tiktok Conversion API
* fix Event Perubahan Status only Status Completed not All Status (FB API & Tiktok API)
* fix Duplicate Data in Event Halaman Invoice FB API & Tiktok API
* fix Data yang direkam di Event Change Status Order disamakan dengan yang di Event Halaman Invoice (FB API)

1.13.6 03 Januari 2024

* feat add error handler in ajax process
* fix bug reminder based on hours
* fix bug updater checker by subscription active/expire
* fix akses & fix error notice in attachment
* fix confirm only by invoice number
* fix list product in affiliate link based on enabled option for affiliate
* fix label name for fb conversion to meta conversion api
* fix bug reminder recurring, 
* fix some error notice
* fix top 10 statistic limit data, 
* fix labeling status license
* fix bug button renewall not showing
* fix bug bumpsales product
* fix bug display payment method in mobile and update missing bank logo, add postal code in address info
* fix bug coupon usage limit
* fix bug display payment method in mobile
* fix bug notice debug log
* fix data tier affiliate not showing in notification
* fix bug product statistic
* fix bug reminder based on hours
* fix bug reminder repeater sending same notification

1.13.5 10 November 2023

* feat reCaptcha V3 in register page
* fix bug shortcode affiliate data
* fix bug missing icon in detail order, subscription, commission
* fix error notice duitku

1.13.4 28 Oktober 2023

* fix bug display payment channel text in checkout page
* fix bug reminder based on hours
* fix bug error when submit register

1.13.3 25 Oktober 2023

* fix bug surcharge payment duitku

1.13.2 21 Oktober 2023

* fix bug checking license with renewal order

1.13.1 20 Oktober 2023

* add option to show/hide payment label text
* add option to show/hide text info input data (full name, email, password, phone number)
* fix bug requires field setting option fb & tiktok offline conversion

1.13.0 18 Oktober 2023
* add BNC VA payment method Duitku
* updater integration
* add currency USD support
* add feature to show/hide postal code field in checkout page
* add notification free shipping and change display discount value with exclude ongkir
* add option for sending reminder based on hours
* add option for keep dashboard statistic, when redirect after login option is activated
* add feature auto switch api key rajaongkir
* add reCaptcha V3 support for checkout
* add payment confirm notification
* add surcharge fee duitku
* add option to multiple sending reminder notification
* add Facebook & Tiktok Offline Conversion bug coupon & quantity if discount type is precentage
* bug callback error 500 duitku
* bug phone number validation input
* bug affiliate tier not showing in email order completed
* bug order with self affiliate
* bug renew license subscription, paging & filter subscription member area, validate license if subscription expired
* bug custom menu & icon link member area
* reminders are still sent even though the order status has been extended
* improved slow queries
* member area menu
* disable field qty for bump product & change text field in checkout form
* delete limit date on update, and issue coupon with limit date not showing in parent coupon affiliate
* bug linked affiliate
* change label text log status order
* responsive payment option on mobile
* php warning license
* fix missing bank logo moota
* bug product statistic
* bug coupon precentage calculation

1.12.0 12 February 2023
* PHP 8.0 compatibility
* Moota improvement
* Fix issue in bump product
* Fix issue with subscription and license
* Fix issue when license input
* Fix issue in logging system
* Fix issue in redirect user after login

1.11.6 1 November 2022
* Fix license issue
* Fix order status time log

1.11.5
* Enable to hide affiliate contact info
* Add capability to manager role to access order data
* Add support shortcode notification per product
* Update duitku payment channel
* Fix bug in affiliate commission notification
* Fix layout with renewal checkout page
* Fix issue with order log status

1.11.4 14 September 2022
* Add order status log
* Disable to create affiliate coupon on expired coupon
* Fix checkout image issue in mobile view
* Fix checkout page issue if coupon field is not displayed
* Fix issue in thankyou page for design ver 2
* Fix bug issue in postcode validation

1.11.3 5 September 2022
* Fix warning issue in user group
* Fix issue in background image for new checkout design

1.11.2 5 September 2022
* Fix critical issue in member area

1.11.1 3 September 2022
* Increase performance
* Fix issue with order template
* Fix issue with renewal subscription

1.11.0 1 September 2022
* Add bump sales feature
* Add checkout design v2
* Fix bug in commission chart
* Able to use wallet in subscription renewal

1.10.3 21 July 2022
* Fix issue with license registration
* Fix issue with follow up order action

1.10.2 18 July 2022
* Add mandirilivin from moota payment
* Add duitku payment channel
* Enable quantity in digital product checkout
* Fix sales chart in mobile for responsive purpose
* Fix issue in user group
* Fix issue broken link in notification menu
* Fix issue in using coupon for 100% discount
* Fix issue in order followup data
* Fix issue in social proof notification
* Fix text issue

1.10.1 17 April 2022
* Fix currency translation
* Fix user group not showing on dropdown

1.10.0 3 April 2022
* Add notification setting in each product based on order status
* Add more translated string
* Hide priority value in user group setting [deprecated]

1.9.0 27 Maret 2022
* Add confirmation link on saled detail popup
* Add english translation
* Add currency option in sejoli setting
* Display date in commission page
* Auto display coupon form in checkout page
* Fix phone number validation on physic product checkout

1.8.2 18 Februari 2022
* Change from wp_safe_remote_post to wp_remote_post based on WordPress latest update

1.8.1 17 Februari 2022
* Fix sales graph bar issue
* Fix issue login with new username type

1.8.0 03 January 2021
* Add feature to hide affiliate detail in affiliate network tree
* Add feature to generate username based on email address cleaner than before
* Fix query date in sales and commission based on update_at
* Fix enable username field in registration page
* Fix notification for affiliate after order created
* Fix error user invalid for new user in physical product checkout form

1.7.0 29 November 2021
* Add feature to change user's affiliate
* Display available shortcodes in notification
* Fix wrong unique code in checkout form
* Fix new order notification for affiliate
* Fix logo issue with theme conflict
* Fix duitku bug

1.6.5 1 November 2021
* Add several filters to pay commission
* Add more statistic widgets in admin area
* Fix error when checkout with donation product
* Fix error notification with duitku
* Fix error filter data in subscription
* Fix attachment error in notification
* Fix registration notification email
* Fix bug in affilate network
* Fix notification affiliate for new order
* Fix error checkout for new user
* Fix issue when checking license


1.6.4 16 October 2021

* Modify affiliate commission payment process
* Fix registration email
* Fix duitku error notification
* Fix error filter data subscription
* Fix attachment only being sent to customer

1.6.3.1 13 September 2021
* Fix affiliate wrong tier key

1.6.3 10 September 2021
* Add feature for admin to see any user's network tree
* Fix license default status
* Fix confirmed payment notification after submit

1.6.2
* Add affiliate network submenu
* Fix subscription reminder

1.6.1.1
* Fix unneeded comma

1.6.1
* Fix security issue
* Fix select2 width
* Fix coupon bug

1.6.0.2
* Fix wrong order status when invoice created

1.6.0.1
* Change span duration on checkout process, from 5 seconds to 1 second
* Remove COD payment option, it will be used later for Sejoli COD system
* Fix payment logo size in mobile
* Fix wrong message when do manual reminder function

1.6.0 01 Agustus 2021
* Add group name in user profile page
* Add option to disable debug
* Add note column in CSV exported file
* Fix typo in notification text
* Fix debug log check writeable permission
* Fix duitku payment channel
* Fix product selectbox in mass notification page

1.5.6.2 21 Mei 2021
* Change duitku sandbox url

1.5.6.1 11 Mei 2021
* Fix small issue in sejolisa_get_sensored_string function

1.5.6 25 April 2021
* Add BSI ( Bank Syariah Indonesia ) for moota
* Add several payment method for duitku
* Add API support from sejoli
* Enhance log folder protection
* Fix license checking problem
* Fix several warning issues

1.5.5 17 Maret 2021
* Add option to modify member area color
* Fix wrong date on order management data
* Add hook for Sejoli JV addon later

1.5.4 5 Maret 2021
* Update composer library
* Fix bug on comparing expired data with max renewal days
* Optimize script
* Add admin message when WP_DEBUG is active
* Remove cancelled order from total order in daily and monthly statistic

1.5.3.4 7 Februari 2021
* Fix CRITICAL ISSUE with physical and renew checkout

1.5.3.3 5 Februari 2021
* Add option to limit reorder, prevent multiple invoice created
* Add blockUI when order created completed to prevent multiple invoice created

1.5.3.2 14 Januari 2021
* Fix notice when creating an order
* Fix bug when limiting affiliate link

1.5.3.1 12 Januari 2021
* Add OVO and GOPAY in moota transaction method
* Add woowandroid ver 2 whatsapp notification library
* Add starsender whatsapp notification library
* Add option to enable or disable affiliate menu
* Set default value to sejoli_enable_affiliate_if_already_bought to FALSE

1.5.3 11 Januari 2021
* Add option to enable affiliate link only after buy the product
* Enhance order export data, add more information link shipment, courier and variants
* Add option to define maximal renewal day
* Add feature to add subscription export data
* Fix issue in social proof

1.5.2.4 08 December 2020
* Fix problem is renewing order
* Add detail info in checkout form

1.5.2.3 13 November 2020
* Fix problem is social proof code generator

1.5.2.2 10 Oktober 2020
* Fix problem in affiliate order

1.5.2.1 9 Oktober 2020
* Fix bug in affiliate link that render link is not valid
* Fix bug in social proof code auto-fill in

1.5.2 9 Oktober 2020
* Enable product to not be affiliated
* Enhance confirmation form
* Enhance confirmation attachment
* Fix bug in dimesale by time
* Fix bug in affiliate when total user number over 10.000
* Fix bug in affiliate link

1.5.1.1 7 September 2020
* Add option to display product image in social proof
* Add several payment chanels in duitku payment gateway
* Change RETAIL logo in duitku
* Modify expiryPeriod value in callback duitku
* Fix problem in process_callback in duitku
* Fix problem in facebook pixel

1.5.1 4 September 2020
* Add coupon parameter to affiliate link
* Enable auto-fill coupon in checkout if there is any coupon data in cookie or url request
* Add current time value when updating affiliate commission status, it will prevent to update all commission data
* Remove confirmation image after email confirmation sent
* Fix bug when using free-shipment coupon
* Fix bug when using elementor with 'sejoli-member-area' template
* Add login form in blocked renewal page

1.5.0.1 31 Agustus 2020
* Fix bug in generating popup code

1.5.0 31 Agustus 2020
* Add social proof popup
* Add reset data function
* Fix bug in confirmation process

1.4.3.2 09 Agustus 2020
* Fix bug after checkout form completed that redirected to member-area

1.4.3.1 07 Agustus 2020
* Fix bug after checkout form completed

1.4.3 07 Agustus 2020
* Translate bank ID from Moota to real bank name
* Add more bank logo based on Moota Bank ID
* Fix bug with autoresponder register via birdsend
* Fix problem with cannot access register page

1.4.2.3 18 Juli 2020
* Remove debugging in checkout page

1.4.2.2 17 Juli 2020
* Fix bug in search page, conflict with landingpress

1.4.2.1 9 Mei 2020
* Remove bug in generating username

1.4.2 8 Mei 2020
* Reduce shipping cost from grand total order in user-group commission calculation
* Redefine user login methods
* Add method to generate username
* Fix problem when updating order status in duitku
* Change retail logo in duitku payment gateway
* Register new endpoint for renew Subscription
* Fix problem in limiting coupon usage
* Fix problem that not displaying product quantity and sub total order
* Add option to enable customer to fill shipment cost

1.4.1.2 = 9 Mei 2020
* Fix follow up problem
* Fix content problem in woowa and WooWandroid
* Fix coupon use for own-self coupon
* Fix code access to closed product
* Fix wrong payment instruction for manual payment
* Fix condition logic in user group update field
* Restructurize duitku.com fields

1.4.1.1 = 9 Mei 2020
* Fix user group condition in checkout page

1.4.1 = 8 Mei 2020
* Add reset license in web installation
* Add new order notification for affiliate
* Add max value unique code for each manual transaction. Max value is 999
* Enhance request register license with extra detail in response
* Enhance request validate license with extra detail in response

1.4.0.4 = 7 Mei 2020
* Fix non-affiliate coupon bug in member area
* Fix donation problem

1.4.0.3 = 5 Mei 2020
* Fix non-affiliate coupon bug in member area

1.4.0.2 = 4 Mei 2020
* Panic update!

1.4.0.1 = 4 Mei 2020
* Fix problem in affiliate link
* Fix problem warning info in functions/user.php
* Fix problem in registering quarterdaily cron job

1.4.0 = 30 April 2020
* Add user group function
* Add multiple couriers
* Add new respond in sejoli-license and sejoli-validate-license request
* Fix compatibility with WPUF
* Fix compatibility with yoast

1.3.3 = 18 April 2020
* Add new cron schedules to fix auto cancel order, quarterdaily cron time
* Add new function in payment confirmation to check by order amount
* Fix problem in license checking
* Fix problem in checkout form, checkout button wouldn't be unblocked after displaying error message

1.3.2.2 = 07 Maret 2020
* Fix problem in thank you page that display free ongkir even doesn't use any coupon

1.3.2.1 = 06 Maret 2020
* Fix problem in confirmation page

1.3.2 = 05 Maret 2020
* Display total commission based on status ( paid commission, unpaid commission and potential commission)
* Add member message
* Fix facebook pixel problem with affiliate
* Fix free shipping coupon

1.3.1
* Fix problem with duitku.com, that the total must be integer instead float
* Add callback url field in duitku.com setup
* Change yearly statistic to last 12 months

1.3.0
* 2nd public release
* Integration with duitku.com
* Integration with learnpress, need add-on
* Add donation/crowdfunding system, need add-on
* Create custom user capability to access admin dashboard
* Modify checkout JS for donation implementation
* Fix many issues

* Add LOG menu under SEJOLI
* Add auto cancel for on-hold order
* Add auto delete log after 30 days
* Add auto delete coupon post data
* Add member area menu logo
* Add option to enable/disable register form
* Add note placeholder option for physical product
* Update error message display when license checking but cant access the license server
* Update third parties composer
* Fix spintax for confirm url
* Fix way to check license from GET to POST
* Fix quantity input for physical product
* Fix detail shipping location in whatsapp
* Remove archive page for content and coupon

1.2.2 = 20 Desember 2019
* Fix invalid coupon, happens in several hosting
* Change member area header menu

1.2.1 = 19 Desember 2019
* Fix problem with wrong subscription detail
* Fix problem with subscription data in thank you page
* Adjust color for expired subscription

1.2.0.1 = 19 Desember 2019
* Fix with shortcode render

1.2.0 = 18 Desember 2019
* Add COD shipment
* Add followup and reminder autonotification
* Add ability to delete coupon
* Update member area
* Update physical product checkout detail
* Display detail district in shipment
* Fix calculate grand total with variant quantity

1.1.9 = 3 Desember 2019
* Add limitation to create affiliate coupon
* Add checkout product description
* Fix bug using manual transaction without unique code

1.1.8.1 = 30 November 2020
* Fix bug broken member menu
* Fix bug with order status payment-confirm can't be updated to completed with payment process

1.1.8 = 29 November 2020
* Fix bug when apply a coupon
* Fix bug when login on a checkout form
* Fix block bug when processing in checkout and confirm
* Fix bug when checking license

1.1.7 = 29 November 2020
* Add note for order
* Add new product menu to manage checkout
* Add customizable member area menu
* Add redirect after login
* Update JS script to block when checkout process
* Update carbonfield library
* Hide payment option if transaction is zero
* Fix problem with woowa integration

1.1.6 = 22 November 2019
* Add code access to closed product checkout
* Add woowa and woowandroid
* Add more function to confirmation process
* Fix problem with JS compatibility

1.1.5 = 17 November 2019
* Add reset password
* Fix problem with JS compatibility

1.1.4 = 13 November 2019
* Add cron job to count coupon usage
* Fix problem to increase coupon usage in checkout
* Fix problem with custom affiliate link
* Fix problem JS compatibility WordPress 5.3 in member menu

1.1.3 = 11 November 2019
* Add affiliasi in admin menu
* Add export affiliasi commission to CSV
* Fix problem with variant product that doesn't use shipping method
* Fix problem with required bank name field in manual transaction

1.1.2 = 8 November 2019
* Add manual bank account for BCA
* Hide courier and shipment cost if shipping option not active
* Fix problem when sending notification when pay commission
* Fix problem when sending notification when order cancelled
* Fix problem when sending notification when order refunded

1.1.1 = 6 November 2019
* Add other bank in manual transaction
* Add option to setup countdown in invoice
* Add option to set redirect if in homepage to member-area
* Add cronjob to fix commission status problem
* Add warning info if coupon is not valid to use
* Fix bug when sending bulk notification
* Fix notice problem when calculating
* Fix bug for commission status
* Remove charset and collation from database setup

1.1.0 = 1 November 2019
* Add mass notification
* Add filter order by grand total
* Add affiliate name in user table
* Fix bug when update commission to paid
* Fix bug for email content
* Fix bug for automatic payment

1.0.3 = 30 October 2019
* Add CSV export order data
* Add notification setup for cancel, refund and paid commission
* Fix problem with cpanel plesk database connection
* Fix problem with counting license number
* Remove password for whatsapp and email
* Remove attachments for email ( later we will add whitelist file extension for this)

1.0.2 = 27 October 2019
* Add video tutorial for access and coupon
* Change text from Bantuan to Tutorial
* Change whatsapp contact to email in thank you page
* Hide sales info in leaderboard for non admin user
* Hide delete action for button
* Fix bug when trigger to display order detail
* Fix bug when display account number
* Fix bug when display affiliate bonus
* fix problem when editing access page with elementor

1.0.1 = 25 October 2019
* Add display affiliate name in checkout
* Add display affiliate name in checkout when apply a coupon
* Fix bug when displaying account number for manual transaction
* Fix bug when updating the option, caused by moota_account_owner field
* Remove attachments if recipient is both admin and affiliate

1.0.0 = 24 October 2019
* First release
