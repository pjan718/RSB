=== WP Post Styling ===
Contributors: joedolson
Donate link: http://www.joedolson.com/donate.php
Tags: css, post, page, custom, styling, admin, mobile, print
Requires at least: 2.5
Tested up to: 3.1-RC5
Stable tag: trunk

Allows you to define custom styles for any specific post or page on your WordPress site. This is particularly useful for journal-style publications which want to provide a unique character for specific articles.

== Description ==

This plugin simply provides a custom field on your WordPress interface where you can add custom styles to be applied only on that page or post. Useful for being able to publish articles with a unique look.

The use you'll get out of this plugin depends on the flexibility of the theme you're using and your own knowledge of CSS (Cascading Style Sheets).

How to use the style library:

1. Add the styles you want on your settings page.
1. Navigate to an article which requires specific styles.
1. Select the library style from the drop down, leaving the style textarea blank.
1. Update or post the new document.

A newly-selected style from the style library will always overwrite any previous hand-written styles. If you wish to alter the library styles for a specific page, you can do this in the textarea after you've saved the page with the style library template. Editing your templates will have no impact on previously saved post-specific styles. 

== Changelog ==

= 1.2.3 = 

* Clean up on deprecated calls
* Switch post meta to private 
* Placed admin styles into separate file
* Bug fix: Custom styles would periodically disappear from post.

= 1.2.2 = 

* Added stripslashes so that styles which require quotes will be consistently usable. (Background images, :before and :after, etc.) 

= 1.2.1 =

* Added option to delete CSS in the style library

= 1.2.0 =

* Added Changelog
* Added ability to edit CSS in the style library
* Updated post interface to use post-2.6 drag-and-drop options
* Made translation ready

= 1.1.0 =

* Added a database to store pre-determined style groups. 
* Corrected a few layout bugs.

== Installation ==

1. Upload the `wp-post-styling` folder to your `/wp-content/plugins/` directory
2. Activate the plugin using the `Plugins` menu in WordPress
3. Go to Settings > WP Post Styling
4. Adjust the WP Post Styling options if necessary. 
5. Set up custom styles for your posts and pages as needed!

== Frequently Asked Questions ==

= I don't really know CSS. Can I use this plugin? =

You really do need to know CSS to get anywhere with this. Given the huge variety in styles provided by WordPress themes, it's impractical to attempt to predict what kinds of styles you might need. 

= The Custom styles I added aren't showing up in my blog -- why not? =

Well, this is just a stab in the dark, but it's possible that the developer of your theme didn't use the WordPress function <code>wp_head</code>, which needs to be in your theme for this plugin to work. 



== Screenshots ==

1. WP Post Styling Settings Page
2. WP Post Styling Custom Styles Box