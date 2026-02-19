# Sinatra #
### A lightweight and highly customizable multi-purpose theme that makes it easy for anyone to create their perfect website.

Community-maintained fork of the original Sinatra theme by [Sinatra Team](https://sinatrawp.com). Includes security fixes, PHP 8.2+ compatibility, WordPress 6.9+ support, and updated WooCommerce templates.

[Download Latest Release](https://github.com/ciorici/sinatra/releases) &nbsp;&middot;&nbsp; [View on WordPress.org](https://wordpress.org/themes/flavor/)

![Sinatra Theme Screenshot](screenshot.jpg)

**Contributors:** [ciorici](https://github.com/ciorici), [sinatrateam](https://github.com/sinatrateam)  
**Tags:** two-columns, right-sidebar, left-sidebar, footer-widgets, blog, news, custom-background, custom-menu, post-formats, sticky-post, editor-style, threaded-comments, translation-ready, custom-colors, featured-images, full-width-template, microformats, theme-options, e-commerce  
**Requires at least:** 6.4  
**Tested up to:** 6.9  
**Requires PHP:** 7.4  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  
**Version:** 1.4.0  

A lightweight and highly customizable multi-purpose theme that makes it easy for anyone to create their perfect website.

## Description ##
Sinatra is a lightweight and highly customizable multi-purpose theme that makes it easy for anyone to create their perfect website. It comes with microdata integration, unlimited colors, multiple layouts and so much more. It's also translatable and built with best SEO practices. It works well with your favorite plugins such as WooCommerce, JetPack, page builders, SEO plugins and others.

This is a community-maintained fork of the original Sinatra theme by Sinatra Team. The original theme was last updated in 2023 and is no longer maintained. This fork includes security fixes, PHP 8.2+ compatibility, WordPress 6.7+ compatibility, and updated WooCommerce support.

Source code: https://github.com/ciorici/sinatra

## Frequently Asked Questions ##
### How to install Sinatra? ###
1. Download the latest release from https://github.com/ciorici/sinatra/releases.
2. Log into your WordPress Dashboard and go to Appearance &raquo; Themes and click the "Add New" button.
3. Click "Upload Theme" and select the downloaded zip file.
4. Click the "Activate" button to activate Sinatra theme on your site.
5. Navigate to Appearance &raquo; Customize to access theme options.

## Changelog ##
### 1.4.0 ###
* Security: Fixed stored XSS in breadcrumbs (CVE-2024-37116).
* Security: Fixed reflected XSS in search forms.
* Fixed: PHP 8.2+ deprecation warnings (dynamic properties).
* Fixed: WordPress 6.9+ deprecation warnings (IE conditional comments).
* Fixed: strip_tags() and version_compare() null parameter deprecations.
* Fixed: Block editor button block appearing full-width.
* Updated: WooCommerce template overrides to latest versions.
* Updated: Replaced WooCommerce template overrides with hooks where possible.
* Removed: IE-only assets (html5shiv, flexibility.js, sinatra-ie stylesheet).
* Removed: Outdated vendor prefixes from CSS source files.
* Added: Gulp build system for CSS/JS compilation and minification.
* Improved: Dropped legacy browser support (IE11).
* Improved: Modern autoprefixer configuration.

### 1.3.0 ###
* Updated: WooCommerce templates.
* Updated: WordPress 6.3 compatibility.
* Updated: Google Fonts list.

### 1.2.1 ###
* Fixed: Square icon displaying on mobile navigation.
* Fixed: Table alignment not working.
* Fixed: Pre-Footer not displaying on homepage.
* Fixed: Post format "Link" incorrect links.
* Updated: Google Fonts list.
* Improved: CSS enhancements.

### 1.2.0 ###
* Added: Responsive visibility for Sticky header.
* Fixed: Sticky sidebar option in combination with sticky header.
* Fixed: WooCommerce archive title in breadcrumbs.
* Fixed: SVG Icons not working in Customizer widgets.
* Updated: Google Fonts list.
* Improved: Replaced font icons with SVGs.
* Improved: Performance.
* Improved: CSS enhancements.
* Improved: Accessibility.

### 1.1.6 ###
* Fixed: Headings on Block Editor display incorrectly.
* Fixed: Background and Content Background color incorrect for Boxed and Boxed Content layout.
* Fixed: Displaying "Blog" in breadcrumbs on single pages.
* Fixed: Breadcrumbs background displaying featured image on product pages.
* Fixed: Header Cart widget - Variable product displaying wrong price and name.
* Improved: TablePress compatibility.

### 1.1.5 ###
* Updated: WooCommerce templates.
* Fixed: Mobile menu visibility.
* Fixed: SSL support for uploads folder.
* Improved: Code formatting.

### 1.1.4 ###
* Updated: WordPress 5.5 compatibility.
* Fixed: Range control in Customizer not working.
* Fixed: Page Header background color overlay not working.
* Improved: Block Editor styles to match frontend design.

### 1.1.3 ###
* Added: 'sinatra_entry_meta_post_type' filter that allows post meta tags to be displayed on custom post types.
* Added: Sticky header option.
* Added: New Main Footer column layout: 1/3 + 2/3.
* Added: New Main Footer column layout: 2/3 + 1/3.
* Improved: CSS enhancements.

### 1.1.2 ###
* Added: Option to control sidebar position on smaller screens.
* Fixed: CSS issue with mobile (hamburger) menu colors.
* Fixed: CSS issue with copyright menu not displaying on mobile devices.
* Fixed: Breadcrumbs Posts page title hard coded to 'Blog' on single pages.
* Updated: Default values for transparent header.
* Updated: Google Fonts list.
* Improved: Block Editor styles to match frontend design.
* Improved: CSS enhancements.

### 1.1.1 ###
* Added: Transparent Header - option to set alternative logo.
* Added: Transparent Header - options for logo size and spacing.
* Added: Transparent Header - color options.
* Added: Option to enable or disable Transparent Header on individual posts/pages.
* Fixed: Block Compatibility: Button Block Color Specificity.
* Fixed: Various alignwide and alignfull issues with Block libraries.
* Fixed: Alignfull blocks cover up the post meta.
* Updated: Google Fonts list.
* Improved: Block Editor styles to match frontend design.
* Improved: CSS enhancements.

### 1.1.0 ###
* Added: Blog - Horizontal Layout and options to customize the layout.
* Added: Option to choose image size for Blog / Archive layout.
* Added: Breadcrumbs section in Customizer.
* Added: Display options for Breadcrumbs.
* Added: Display options for Hero.
* Added: Display options for Pre Footer Callout.
* Added: Option for Page Header alignment.
* Added: Option for Page Header spacing.
* Added: Option for Page Title layout for Single Post pages.
* Added: Option to display "Shares" in Post Meta Elements when Social Snap is enabled.
* Added: Option to show avatar and icons in post meta in Blog &raquo; Blog Page/Archive &raquo; Show.
* Added: Option to show last updated date in Blog &raquo; Single Post &raquo; Post Elements.
* Added: Reset to default values in customizer controls.
* Added: Action before and after hooks for Top Bar, Header and Copyright widgets.
* Added: Theme support for responsive embeds.
* Added: Database Upgrader class.
* Added: Flexibility.js to improve flexbox browser support.
* Fixed: Gallery Block align center not working.
* Fixed: Breadcrumbs and Google Search Console issue.
* Fixed: Hero &raquo; Device Visibility not working.
* Fixed: Main Header &raquo; Background Image Overlay Color not working.
* Fixed: Main Navigation disappears when screen is resized.
* Fixed: Main Navigation &raquo; Typography &raquo; Font Size issue.
* Fixed: Footer &raquo; Pre Footer &raquo; Call to Action &raquo; Typography not working.
* Fixed: Mobile menu dissappears when scrolling.
* Fixed: Links in Sinatra widgets always open in same tab.
* Updated: Appearance » Sinatra Theme page.
* Updated: Google Fonts list.
* Improved: Browser compatibility.
* Improved: Responsive styling.
* Improved: CSS enhancements.
* Improved: WPML plugin compatibility.

## Copyright ##

Sinatra WordPress Theme, Copyright 2025 ciorici
Originally created by Sinatra Team (https://sinatrawp.com).
Sinatra is distributed under the terms of the GNU GPL.

Sinatra bundles the following third-party resources:
Feather Icons, https://feathericons.com/
Copyright (c) 2013-2017 Cole Bemis, MIT License, http://www.opensource.org/licenses/mit-license.php

ImagesLoaded, https://imagesloaded.desandro.com/
Copyright (c) 2019 David DeSandro, MIT License, http://www.opensource.org/licenses/mit-license.php

Breadcrumb Trail, https://github.com/justintadlock/breadcrumb-trail
Copyright (c) 2008-2017 Justin Tadlock, GNU GPL v2 or later License, https://opensource.org/licenses/GPL-2.0

WP Color Picker Alpha, https://github.com/kallookoo/wp-color-picker-alpha
Copyright (c) Sergio GNU GPL v2 or later, https://opensource.org/licenses/GPL-2.0

Normalize.css, https://necolas.github.io/normalize.css/
Copyright (c) Nicolas Gallagher and Jonathan Neal, MIT License, http://www.opensource.org/licenses/mit-license.php

Select2, https://select2.org/
Copyright (c) 2012-2017 Kevin Brown, Igor Vaynberg, and Select2 contributors, MIT License, http://www.opensource.org/licenses/mit-license.php

Screenshot image by Zachary Nelson (StockSnap.io): https://stocksnap.io/photo/QYPJ92FECM
CC0 License, https://creativecommons.org/publicdomain/zero/1.0/
