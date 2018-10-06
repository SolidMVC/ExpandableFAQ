# ExpandableFAQ
It’s a MIT-licensed (can be used in premium themes), high quality, native and responsive WordPress plugin to create and view expandable F.A.Q.'s

Contributors: KestutisIT
Donate link: https://profiles.wordpress.org/KestutisIT
Website link: https://wordpress.org/plugins/expandable-faq/
Tags: faq, faqs, Frequently Answered Questions, expand, collapse
Requires at least: 4.6
Tested up to: 4.9
Requires PHP: 5.4
Stable tag: trunk
License: MIT License
License URI: https://opensource.org/licenses/MIT

It’s a MIT-licensed (can be used in premium themes), high quality, native and responsive WordPress plugin to create and view expandable F.A.Q.'s


== Description ==

**First** - differently than any other similar plugin, this plugin is based on MIT license, which is a holly-grail for premium theme authors on i.e. ThemeForest or similar marketplaces.
Differently to standard GPLv2 license you are not required to open-source your theme and you **CAN** include this plugin into your premium websites bundle packs.
I do say here **bundle packs**, because you should never have an F.A.Q. to be a part of your theme, because that would be a bad idea - you need to leave your customers a flexibility for the future scale:
What if your customers will decide later go with some kind of fancy **knowledge base** system like in Change.org `/help/` part or Envato.com `Support` part - if your customer will grow that big,
he won't need to have F.A.Q. plugin anymore on their website, he will want to replace it with that fancy **knowledge base** system.
So my advise is to include this plugin in your bundle pack's `/Optional Plugins/` folder, so that you can tell about in the installation instructions, but make it fully independent from your theme.

**Second** - this plugin is fully **MVC + Templates** based. This means that it's code is not related at all to it's UI,
and that allows you easily to override it's UI templates and Assets (CSS, JS, Images) by your theme very easily (and there is detailed step-by-step instructions given how to do that).
This means that you making a theme to be what the theme has to be - a UI part of your website, nothing more.

**Third** - it is much more secure than any other plugin's on the market. It is based on top-end S.O.L.I.D. coding principle with input data validation with data-patterns, output escaping.

**Forth** - this plugin is scalable – it’s source code is fully object-oriented, clean & logical, based on MVC architectural pattern with templates engine, compliant with strict PSR-2 coding standard and PSR-4 autoloaders, and easy to understand how to add new features on your own.

**Fifth** - this plugin works well with big databases & high-traffic websites – it is created on optimal BCNF database structure and was tested on live website with 1M customers database and 500,000 active daily views.

**Sixth** - it does support official WordPress multisite as network-enabled plugin, as well as it does have support WPML string translation.
At this point, if you need more than one language, I'd strongly advise to go with official WordPress multisite setup, because it is free, it is official (so you will never need to worry about the future support), and, most important - WordPress multisite is much more suitable for websites that needs to scale. You don't want to have that additional translation bottle-neck code layer to be processed via database.

**Seventh** - it has nice user experience - it's has a default design, it does allow you to have more than one F.A.Q. item open at the same time - so it don't have that annoying `accordion` feature.

**But the most important** is that this plugin is and always be **ads-free**. I personally really hate these **freemium**, **ads-full** or **tracking** plugins which makes majority
of the plugins on w.org plugins directly (and, actually, many of premium marketplaces). So this is the key features we always maintain:
1. Never track your data (nor even by putting some kind of GDPR-compliance agreement checkbox, like `Error Log Monitor` plugin),
2. Never make it pseudo-ads-full (even such a big plugins like `WooCommerce` or `Contact Form 7` has nearly 80% of their home screen or 20% of their main buttons about `how to install \ buy other plugins`
- this is a really ugly behavior of pushing-more and going to Facebook-like business, where you get like drug-addicted to company products).

The goal of this plugin is to full-fill the needs of website-starter, that needs a great tool which can last him for many years until it will grow that big so he would grow-out current plugins and would need some kind of different plugins.

And, I believe, that many other developers had the same issue when tried to create their first premium theme or set-up a website for their client. Starting with the issues with license type to the moment when F.A.Q. is `hardcode` into theme code.

So I wanted to help all these developers to save their time, and I'm releasing this plugin for you to simplify your work. And I'm releasing it under MIT license, which allows you to use this plugin your website bundle without any restrictions for both - free and commercial use.

Plus - I'm giving a promise to you, that this plugin is and will always be 100% free, without any ads, 'Subscribe', 'Follow us', 'Check our page', 'Get Pro Version' or similar links.

Finally - the code is poetry - __the better is the web, the happier is the world__.

- - - -
**Live Demo**
[Expandable FAQ (Live Demo)](http://nativerental.com/cars/faq/ "Expandable FAQ (Live Demo)")


# Installation

This section describes how to install the plugin and get it working.

1. Upload `ExpandableFAQ` (or `expandable-faq`) to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
2.1. If your theme **does not* support FontAwesome icons, please **enable** FontAwesome in Expandable FAQ -> Settings -> "Global" tab.
3. Go to admin menu item `Expandable FAQ` -> `FAQ Manager` and add F.A.Q.'s.
4. Now create a page by clicking the [Add New] button under the page menu.
5. Add `[expandable_faq display="faqs" layout="list"]` shortcode to page content and click on `[Publish]` button.
6. In WordPress front-end page, where you added search shortcode, you will see expandable F.A.Q.'s.
7. Congratulations, you're done! We wish you to have a pleasant work with our Expandable FAQ for WordPress.


# Frequently Asked Questions

= Does it allows to have more than one F.A.Q. item expanded? =

Yes, differently than other similar F.A.Q. plugins, this plugin does not use the annoying `accordion` feature, and allows you to have more than one FAQ item expanded.

= Does it support URL parameters and hashtags? =

Yes, if your F.A.Q. ID is i.e. `42` (you can get your F.A.Q. ID from `Expandable FAQ` -> `FAQ Manager`), then you can go
to your website's FAQ page and make automatically expand specific FAQ with a page focus to that F.A.Q. via this URL structure:

`
<YOUR-SITE>.com/<FAQ-PAGE>/?expand_faq=[ID]#faq-[ID]
`


# Screenshots

![1. Expandable FAQ - Front-End F.A.Q.'s](https://ps.w.org/expandable-faq/assets/screenshot-1.jpg)
![2. Expandable FAQ - Admin FAQ Manager](https://ps.w.org/expandable-faq/assets/screenshot-2.jpg)
![3. Expandable FAQ - Admin Global Settings](https://ps.w.org/expandable-faq/assets/screenshot-3.jpg)
![4. Expandable FAQ - Admin Import Demo](https://ps.w.org/expandable-faq/assets/screenshot-4.jpg)
![5. Expandable FAQ - Admin Plugin Status](https://ps.w.org/expandable-faq/assets/screenshot-5.jpg)
![6. Expandable FAQ - Admin User Manual](https://ps.w.org/expandable-faq/assets/screenshot-6.jpg)



# Changelog

= 6.0 =
* Initial release! Based on S.O.L.I.D. MVC Engine, Version 6 (without extensions).


# Upgrade Notice

= 6.0 =
* Initial release!
