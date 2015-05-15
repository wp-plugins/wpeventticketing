=== WP Event Ticketing ===
Contributors: vegasgeek, jrfoell, toddhuish, 9seeds
Donate Link: http://9seeds.com/donate/
Tags: event, events, ticket, tickets, ticketing, attend, attendee, attending, attendance, conference, wordcamp, admission, entry
Requires at least: 2.8
Tested up to: 4.2.2
Stable tag: 1.3.4
License: GPLv2 or later

Sell tickets for an event directly from your WordPress website.

== Description ==
WP Event Ticketing makes it easy for you to sell tickets for a single event directly from your WordPress website.

== Changelog ==
= 1.3.4 =
* Fix SSL v3 paypal errors

= 1.3.3 =
* Update link for Paypal instructions
* Add new FAQs
* Removed extra closing div that was breaking instructions page

= 1.3.2 =
* Fix date calculation bug for ticket sold times

= 1.3.1 =
* Fix reporting bug with multiselect options on tickets

= 1.3 =
* Cleanup HTML on settings page
* Change attendee notification behavior
* Add exclusion rules to attendee page shortcode

= 1.2.4 =
* Duplicate calls to getAttendees() was causing excessive memory usage.

= 1.2.3 =
* Bugfix display currency was previously consistently off everywhere, now fixed

= 1.2.2 =
* Changed Paypal parameters such that customers without a Paypal account can still purchase tickets
* Display Currency throughout the entire program is consistent

= 1.2.1 =
* Fixed Syntax error

= 1.2 =
* Add attendee page shortcode
* Change thank you page display to be links
* Change thank you page links and purchaser email and admin summary email to contain ticket names of what was purchased
* Add package names and coupon names to attendee list and export pages

= 1.1.7 =
* Bugfix don't encode & to &amp; in emails...bad llama.

= 1.1.6 =
* Bugfix more clearly display revenue and discounted coupon revenue in report
* Bugfix multi ticket packages can now have their attendees deleted properly
* Bugfix Setups with multiple ticket types can now edit and delete attendees properly
* Add super secret debug functionality

= 1.1.5 =
* Bugfix don't format prices and reformat before sending to paypal. Causes errors when sending 1,000 instead of 1000
* Bugfix fix totals not showing up in emails
* Bugfix When shortcode hook is called twice on single WP request redirect from paypal don't let it run twice
* Bugfix Force SSL v3 for Curl calls to paypal re: http://curl.haxx.se/mail/lib-2010-06/0169.html (thanks @wombatcombat)
* Add check so if all the tickets for a package are deleted the package is de-activated

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

= 1.1.7 =

This upgrade fixes a small issue with email formatting. See changelog for detailed list.
