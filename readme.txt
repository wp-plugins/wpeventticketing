=== WP Event Ticketing ===
Contributors: toddhuish, vegasgeek, stastic
Tags: event, events, ticket, tickets, ticketing, attend, attendee, attending, attendance, conference, wordcamp, admission, entry
Requires at least: 2.0
Tested up to: 3.0.1
Stable tag: 1.0

Use WPEventTicketing to manage and sell ticket for an event from your WordPress website.

== Description ==

The WPEventTicketing plugin makes it easy to sell tickets to an event directly from your
WordPress website.

Contains the following features:

* Collect payments payment via paypal.
* Set total attendance limit.
* Multiple ticket types. For example, ticket type A includes a t-shirt while ticket type B2 does not.
* Custom ticket options. This allows you to decide what information you want ticket purchasers to
provide. For example, name, address, shirt size, twitter handle, etc...
* Create ticket packages. For example, early bird specials. Ticket packages can be used to give
a discount to people who place their order during a certain time.
* Create coupons to give discounts to individuals.
* Send email to purchaser upon order completion.
* Reporting page shows total sales and income broken down by package, coupons used and tickets sold.
* Export attendee data to a CSV file.


== Installation ==

1. Upload `wpeventticketing` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set up your Ticket Options
4. Create a new Ticket and select which ticket options to include
5. Create a Ticket Package
6. Set your max attendance
7. Enter your Paypal credentials
8. Set up your email messaging
9. Create a blank page and add the shortcode `[wpeventticketing]`



== Frequently Asked Questions ==

= Can I run multiple events at one time? =

Not at this time. 

== Screenshots ==

1. Reporting page shows total earnings, coupons and ticket sales. Graph shows breakdown of tickets available and sold.
2. Ticket Options are used to collect information from event attendees.
3. Select which options to include for each ticket type.
4. Set all the options for a package.
5. Create coupons to give buyers a flat rate or percentage discount on their purchase.
6. Manage the messaging that gets displayed after a ticket purchase and the email to the purchaser.


== Changelog ==

= 1.1 =
* Fix MS compatibility bug where defaults wouldn't load on new blog creation
* Allow selection of currency type
* Added more notifications to UI as things are added/edited/deleted
* Editing a coupon code doesn't create a new coupon and leave the old one there as well
* Edits for extra styling on purchase form (thanks Ryan!)
* Cleanup of new ticket creation
* Add i18n capabilities to plugin
* Add multi select type to ticket options

= 1.0 =
* Initial release

