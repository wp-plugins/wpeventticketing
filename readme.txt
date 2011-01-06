=== WP Event Ticketing ===
Contributors: toddhuish, vegasgeek, stastic
Tags: event, events, ticket, tickets, ticketing, attend, attendee, attending, attendance, conference, wordcamp, admission, entry
Requires at least: 2.8
Tested up to: 3.0.4
Stable tag: 1.1.4

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
2. Activate the plugin through the 'Plugins' menu in your WordPress dashboard
3. Get a Paypal API Signature (<a href="https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_NVPAPIBasics#id084E30I30RO">Instructions can be found here</a>)
4. Set up your Ticket Options
5. Create a new Ticket and select which ticket options to include
6. Create a Ticket Package
7. Set your max attendance
8. Enter your Paypal credentials
9. Set up your email messaging
10. Create a blank page and add the shortcode `[wpeventticketing]`

We also have a <a href="http://vimeo.com/18491170">walk-through video</a> that explains how to setup WP Event Ticketing.

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

= 1.1.4 =
* Bugfix Default permalink sites (?page_id=<number>) no longer cause illegal URLs to be generated
* Bugfix Clicking on delete then cancel doesn't cause ticket/package to still be deleted

= 1.1.3 =
* Bugfix all prices on front end went to $0.00

= 1.1.2 =
* Bugfix for coupons not working if permalink is default style (?page_id=<number>)
* Spelling fixes
* Bugfix where menu wouldn't show up if thesis was installed
* Make currency output match chosen currency

= 1.1.1 =
* Bugfix for missing </div> in form

= 1.1 =
* Fix MS compatibility bug where defaults wouldn't load on new blog creation
* Allow selection of currency type
* Added more notifications to UI as things are added/edited/deleted
* Editing a coupon code doesn't create a new coupon and leave the old one there as well
* Edits for extra styling on purchase form (thanks @RyanKelln)
* Cleanup of new ticket creation
* Add multi select type to ticket options
* Pre populate ticket with name/email entered in at time of ticket purchase
* Fix bug if no coupon use quantity entered
* Add explicit on/off switch for packages to display or not

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.1.4 =

This upgrade adds a couple new features and fixes a few small issues. See changelog for detailed list.
