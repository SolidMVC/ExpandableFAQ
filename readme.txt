=== Expandable FAQ ===
Contributors: KestutisIT
Donate link: https://profiles.wordpress.org/KestutisIT
Website link: https://wordpress.org/plugins/expandable-faq/
Tags: collapsible, expandable, faqs, expand, collapse
Requires at least: 4.6
Tested up to: 5.2
Requires PHP: 5.6
Stable tag: trunk
License: MIT License
License URI: https://opensource.org/licenses/MIT

It’s a MIT-licensed (can be used in premium themes), high quality, native and responsive WordPress plugin to create and view expandable F.A.Q.'s


== Description ==

**First** - differently than any other similar plugin, this plugin is based on MIT license, which is a holly-grail for premium theme authors on i.e. ThemeForest or similar marketplaces.
Differently to standard GPLv2 license you are not required to open-source your theme and you **CAN** include this plugin into your premium websites bundle packs.
I do say here **bundle packs**, because you should never have an F.A.Q. to be a part of your theme, because that would be a bad idea - you need to leave your customers a flexibility for the future scale:
What if your customers will decide later go with some kind of fancy **knowledge base** system like in Change.org `/help/` part or Envato.com `Support` part - if your customer will grow that big, he won't need to have F.A.Q. plugin anymore on their website, he will want to replace it with that fancy **knowledge base** system.
So my advise is to include this plugin in your bundle pack's `/Optional Plugins/` folder, so that you can tell about in the installation instructions, but make it fully independent from your theme.

**Second** - this plugin is fully **MVC + Templates** based. This means that it's code is not related at all to it's UI, and that allows you easily to override it's UI templates and Assets (CSS, JS, Images) by your theme very easily (and there is detailed step-by-step instructions given how to do that).
This means that you making a theme to be what the theme has to be - a UI part of your website, nothing more.

**Third** - it is much more secure than any other plugin's on the market. It is based on top-end S.O.L.I.D. coding principle with input data validation with data-patterns, output escaping.

**Fourth** - this plugin is scalable – it’s source code is fully object-oriented, clean & logical, based on MVC architectural pattern with templates engine, compliant with strict PSR-2 coding standard and PSR-4 autoloaders, and easy to understand how to add new features on your own.

**Fifth** - this plugin works well with big databases & high-traffic websites – it is created on optimal BCNF database structure and was tested on live website with 1M customers database and 500,000 active daily views.

**Sixth** - it does support official WordPress multisite as network-enabled plugin, as well as it does have support WPML string translation.
At this point, if you need more than one language, I'd strongly advise to go with official WordPress multisite setup, because it is free, it is official (so you will never need to worry about the future support), and, most important - WordPress multisite is much more suitable for websites that needs to scale. You don't want to have that additional translation bottle-neck code layer to be processed via database.

**Seventh** - it has nice user experience - it's has a default design, it does allow you to have more than one F.A.Q. item open at the same time - so it don't have that annoying `accordion` feature.

**But the most important** is that this plugin is and always be **ads-free**. I personally really hate these **freemium**, **ads-full** or **tracking** plugins which makes majority of the plugins on w.org plugins directly (and, actually, many of premium marketplaces). So this is the key features we always maintain:
1. Never track your data (nor even by putting some kind of GDPR-compliance agreement checkbox, like `Error Log Monitor` plugin),
2. Never make it pseudo-ads-full (even such a big plugins like `WooCommerce` or `Contact Form 7` has nearly 80% of their home screen or 20% of their main buttons about `how to install \ buy other plugins`
- this is a really ugly behavior of pushing-more and going to Facebook-like business, where you get like drug-addicted to company products).

The goal of this plugin is to full-fill the needs of website-starter, that needs a great tool which can last him for many years until it will grow that big so he would grow-out current plugins and would need some kind of different plugins.

And, I believe, that many other developers had the same issue when tried to create their first premium theme or set-up a website for their client. Starting with the issues with license type to the moment when F.A.Q. is `hardcoded` into theme code.

So I wanted to help all these developers to save their time, and I'm releasing this plugin for you to simplify your work. And I'm releasing it under MIT license, which allows you to use this plugin your website bundle without any restrictions for both - free and commercial use.

Plus - I'm giving a promise to you, that this plugin is and will always be 100% free, without any ads, 'Subscribe', 'Follow us', 'Check our page', 'Get Pro Version' or similar links.

Finally - the code is poetry - __the better is the web, the happier is the world__.

- - - -
== Live Demo ==
[Expandable FAQ (Live Demo)](http://nativerental.com/cars/faq/ "Expandable FAQ (Live Demo)")

== GitHub Repository (for those, who want to contribute via "Pull Requests") ==
[https://github.com/SolidMVC/ExpandableFAQ](https://github.com/SolidMVC/ExpandableFAQ "ExpandableFAQ @GitHub")

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `ExpandableFAQ` (or `expandable-faq`) to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
2.1. If your theme **does not* support FontAwesome icons, please **enable** FontAwesome in Expandable FAQ -> Settings -> "Global" tab.
3. Go to admin menu item `Expandable FAQ` -> `FAQ Manager` and add F.A.Q.'s.
4. Now create a page by clicking the [Add New] button under the page menu.
5. Add `[expandable_faq display="faqs" layout="list"]` shortcode to page content and click on `[Publish]` button.
6. In WordPress front-end page, where you added Expandable FAQ shortcode, you will see expandable F.A.Q.'s.
7. Congratulations, you're done! We wish you to have a pleasant work with our Expandable FAQ for WordPress.


== Frequently Asked Questions ==

= Does it allows to have more than one F.A.Q. item expanded? =

Yes, differently than other similar F.A.Q. plugins, this plugin does not use the annoying `accordion` feature, and allows you to have more than one FAQ item expanded.

= Does it support URL parameters and hashtags? =

Yes, if your F.A.Q. ID is i.e. `4` (you can get your F.A.Q. ID from `Expandable FAQ` -> `FAQ Manager`), then you can go
to your website's FAQ page and make automatically expand specific FAQ with a page focus to that F.A.Q. via this URL structure:

`
<YOUR-SITE>.com/<FAQ-PAGE>/?expand_faq=4#faq-4
`


== Screenshots ==

1. Expandable FAQ - Front-End F.A.Q.'s
2. Expandable FAQ - Admin FAQ Manager
3. Expandable FAQ - Admin Global Settings
4. Expandable FAQ - Admin Import Demo
5. Expandable FAQ - Admin Plugin Updating
6. Expandable FAQ - Admin Plugin Status
7. Expandable FAQ - Admin User Manual


== Changelog ==

= 6.1.5 =
* Abbreviation functions support added as `es(..)`, `at(..)`, `abh(..)`, `eh()`, `ej()`, `et()` for use mostly in templates, but they can also be used in models, if you use observer models that may be generating and returning to controller whole HTML blocks. Also these functions may be valuable for developers, that wants to have their HTML templates (or PHP-enhanced templates) be fully based only on SolidMVC (MIT-licensed), and not WordPress (GPL-licensed), as in this case your templates would be intensively calling only the MIT-licensed SolidMVC micro-framework’s functions. Additionally, this allows you to write a shorter code for your templates, which is easier to read for your designers.
* escBrHTML(..) support added for language interface.
* Minor documentation and code tune-up.

= 6.1.4 =
* Small tune-up, gallery support added to configuration.
* [SITE_URL] BBCode support added for install/import.

= 6.1.3 =
* Redirection bug fixed for updating the plugin from plugins page.
* Left-over CSS classes and PHP code removed.
* Small minor label bug fixed and small renaming done.

= 6.1.2 =
* Fixed wrong admin JS filename issue.
* Improved variable naming to `FAQ_`, where was still missing.
* Other small naming improvements.
* Missing translations added for manuals, demos and FAQ tabs.

= 6.1.1 =
* Escaping added, when necessary to ‘_doing_it_wrong’ calls.
* Network-updating now can be done for 6.1.1 successfully.
* Some additional minor improvements / patches.

= 6.1.0 =
* NumberDropdown bug fixed in StaticFormatter.
* All table classes are now marked as final.
* For ‘getDataFromDatabaseById’ used array($paramColumns) with getValidSelect instead of paramColumn.
* ‘esc_html’, and ‘esc_br_html’ are now used everywhere.
* Import demo and global settings are now fully translated.
* Added support for ‘style’ [0-9]+ shortcode parameter.
* Added a note for data population in status page.
* Replaced ‘getPluginJS_ClassPrefix’ & ‘getPluginJS_VariablePrefix’ with native call.
* ‘StaticCookie’ and ‘StaticSession’ caching model classes improved.
* Fixed issue with network installing when multisite is enabled in WordPress, as well as created workaround until WordPress core bug #36406 will be fixed (read more at [https://core.trac.wordpress.org/ticket/36406]( https://core.trac.wordpress.org/ticket/36406 "WordPress Trac")).
* PHP 5.6 backwards compatibility added.

= 6.0.2 =
* Updating and patching are now separated. FA now loaded by default after install. Populate/drop data url behaviour changed. Some minor improvements.

= 6.0.1 =
* Expandable FAQ is now fully based on Semantic Versioning. Plus some small issues fixed, and small improvements done.

= 6.0 =
* Initial release! Based on S.O.L.I.D. MVC Engine, Version 6 (without extensions).


== Upgrade Notice ==

= 6.0.2 =
* Just drag and drop new plugin folder or click 'update' in WordPress plugin manager.

= 6.0.1 =
* Just drag and drop new plugin folder or click 'update' in WordPress plugin manager.

= 6.0 =
* Initial release!
