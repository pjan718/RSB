=== Wordpress Navigation List Plugin NAVT ===
Contributors: gbellucci, et_ux
Donate link: http://atalayastudio.com
Tags: navigation, menu, breadcrumb, lists, pages, categories, links, navbar, widget, dropdown, avatars, gravatars, graphic links
Requires at least: 2.5
Tested up to: 2.8.2
Stable tag: 1.0.34

Create, organize and manage your WordPress menus and navigation lists by logically grouping your pages, categories, users via a drag'n drop interface.

== Description ==

The __WordPress Navigation Tool (NAVT)__ plugin is a powerful tool designed to provide you with complete control over the creation, styling and contents of your web site's navigation. The plugin gives you the ability to create unique site navigation from your pages, categories and users using a Drag 'n Drop Interface; arrange the items within a group in any arbitrary order. Navigation groups may be composed of any combination of pages, categories, Authors, (Editors, Contributors, Subscribers), internal/external links and list dividers.

= Plugin Features =

* Navigation items can be duplicated and may appear in more than one group. Each item (even if duplicated) can be independently configured.
* List item names (called a menu alias) can be set to a name that differs from the name used as the page title or the category name.
* Create navigation items to be displayed in one of the following format:
    1. Text only
    1. Text over graphics
    1. Text with side graphic
    1. Graphic only
* Group items can be constructed to appear as a hierarchy - parent/child relationships can be formed between all types of items.
* Navigation menus may be styled using NAVT provided CSS classes, standard Word Press classes, or NAVT will apply _user specified CSS classes_.
* Supports BreadCrumb navigation.
* Supports navigation trees. Clicking a parent navigation item on a page reveals all child navigation on the subsequently displayed page.
* Navigation menus can be displayed (or not displayed) on any combination of user selected posts, pages, home page, archives, 404, search pages.
* Theme integration options allow you to insert a navigation group anywhere in your theme (_without editing your theme_).
* Create navigation using HTML Selects. Create multiple selects in a single group by using dividers.
* Embed navigation lists inside your posts and/or pages.
* Privacy settings for all navigation items and entire groups allows you to hide navigation items in a menu if the user is not logged into your site.
* Supports Gravatars for user navigation items.
* Transparently supports Word Press widgets.
* Built in help.
* Backup/Restore functionality.
* Compatible with the Word Press 2.5+ and Word Press MU
* Compatibility tested with IE6/IE7 (pc), Firefox (pc, Opera (pc), Firefox 2 (mac), Firefox 3 Beta 5 (mac), Swiftweasel
* __NAVT IS NOT compatible with Safari__


= Localization =

* English  (en_US)
* German   (de_DE) by [Rico Neitzel](http://www.rizi-online.de "Rico Neitzel")
* Russian  (ru_RU) by [Dmitriy Kostromin](http://www.blog.kostromin.ru "Dmitriy Kostromin")
* Slovak   (sk_SK) by [Tomas Forro](http://pol.proz.com/profile/810781 "Tomas Forro")
* Japanese (ja_JA) by [Yoichi Kinoshita](http://www.ad-minister.net/ "Yoichi Kinoshita")
* Swedish  (sv_SE) by [Mikael Jorhult](http://www.mishkin.se/blog/ "Mikael Jorhult")
* Belarusian (by_BY) by [Marcis Gasuns](http://www.comfi.com "Marcis Gasuns")

  __If you'd like to contribute by offering your translating skills please contact me at greg @ gbellucci . us__

  For more information, help, etc. Visit the [NAVT Home Page](http://atalayastudio.com "navt home page")

= Change Log Information =

Plugin change information is located on the [Installation page](http://wordpress.org/extend/plugins/wordpress-navigation-list-plugin-navt/installation/ "Installation page.")


== Installation ==

_Classic WordPress and WordPress MU Plugin directory_

1. Download the plugin.
1. Unzip the file in the WordPress directory: `/wp-content/plugins/` - __The plugin must reside in its own directory.__
1. Activate the NAVT plugin from the Word Press plugin page.
1. After activating the plugin, go to the `Manage` menu and select the menu tab: __NAVT Lists__ to use the plugin.
1. NAVT requires the use of JavaScript (_it must be turned on in your browser_).

NAVT version 1.0.x+ will convert navigation groups created with previous versions to the new data format used by NAVT 1.0.x+. However, you should backup any current navigation groups using the NAVT plugin you have installed BEFORE installing and activating the new version of the plugin. NAVT backups are forward compatible with NAVT 1.0.x+
Click the ? (help) buttons provided on the NAVT List page to get help. More help is available in the [NAVT Home Page](http://atalayastudio.com "navt home page")
The `doc` directory contains a single page manual explaining the PHP interface function call syntax.


= Release Notes =
*__1.0.34 Release Candidate__ _(July 23, 2009)_

1. Added Belarusian translation provided by Marcis Gasuns.
1. Tested against wp 2.8.2


*__1.0.33 Release Candidate__ _(July 12, 2009)_

1. Adjustments made for user roles.
1. Tested against wp 2.8.1


*__1.0.32 Release Candidate__ _(May 26, 2009)_

1. Fixed a problem in which carriage return/linefeeds are not removed from the JSON protocol string sent back to the client when returning menu group information. The carriage return/linefeed is contained as part of the string returned in a post or page title (new? as of 2.7.1). The carriage return/linefeed prevents the JSON code from correctly parsing the JSON protocol string and as a result the menu group box is not displayed. 


*__1.0.31 Release Candidate__ _(April 3, 2009)_

1. NAVT now detects whether or not anchor urls should be secure or non-secure.
	(__My thanks to Len Wilson__)
   
*__1.0.30 Release Candidate__ _(March 28, 2009)_

1. include file change made for navtadmin.js.php. This is the only other file that requires wp-config.php to operate correctly. 
   (__My thanks to Audi for the help.__)

*__1.0.29 Release Candidate__ _(March 27, 2009)_

1. Added chmod call to enable file writing in the NAVT plugin directory if the wp-root.php file is not present. _Please read the note provided below._

NOTE: __If you are not able to see the NAVT Lists menu item in the Word Press dashboard:__


Due to the number of ways you can now install WordPress it has become more difficult to find the location of the wp-config.php file from the
plugin directory. _If NAVT cannot locate the wp-config.php file it will not work;_ 

To resolve the problem, the file _wp-root.php_ is dynamically created (by the NAVT plugin) and placed the directory where the NAVT plugin is installed. The file contains the name of the directory where you installed Word Press; this enables NAVT to later determine where to find _wp-config.php_. To create the _wp-root.php_ file, NAVT requires WRITE permission in the NAVT installation directory. The plugin will attempt to set the directory permissions (0777) to enable it to write the file and then change the permissions to 0755 once the file has been created. _If it cannot create the file, the plugin will not work_. If you look in the directory where NAVT is installed and you don't see the file: _wp-root.php_, change NAVT's installation directory permissions to 0777 and reload your home page (from your browser). The wp-root.php file should appear. Change the permissions back to 0755 after the file has been created.
If you change your WordPress installation directory, delete the _wp-root.php_ file in the NAVT installation directory so the file can be recreated. 

*__1.0.28 Release Candidate__ _(March 23, 2009)_

1. Fixed the issue with NAVT not correctly including the WordPress file wp-config.php when the wp installation is not the root directory OR if the WP_CONTENT directory has been moved.  

*__1.0.27 Release Candidate__ _(Dec 19, 2008)_

1. Addition of Japanese localization (__Thanks to Yoichi Kinoshita for the help.__)
1. Addition of Swedish localization (__Thanks to Mikael Jorhult for his help.__)
1. Updates for role manager support.
1. Updates to support Word Press 2.7
1. Navigation Groups can be displayed (or not) on selected categories.
1. Bug fixes
1. Removed backup/restore until the next release
1. A few visual updates

*__1.0.26 Release Candidate__ _(May 15, 2008)_

1. Fixes for supporting navt backup when used with Word Press MU

*__1.0.25 Release Candidate__ _(May 14, 2008)_

1. Minor changes for localization.
1. Localization updates for Russian and Slovak. (German is in progress)
1. Minor update that squeezes out space between tags in codeblocks.
1. IE6 CSS adjustments.


*__1.0.23 Release Candidate__ _(May 13, 2008)_

1. Fixed problems with renaming groups.
1. Renaming a group will update any widgets that refer to that group.
1. Added a new misc item called a __code block__ - enables you to insert HTML tags as part of a navigation list. Add images, headers, text, etc. between items.
1. Added a new option that allows all navigation items to carry user entered HTML tags that can be inserted above, before, after or below a navigation item. [see this article](http://atalayastudio.com/archives/39 "navt codeblocks")
1. Fixed an issue with an ampersand in a title.
1. A user CSS class can now be assigned to individual navigation item anchor.
1. Updated the help information.

*__1.0.22 Release Candidate__ _(April 26, 2008)_

1. Added support for the role manager plugin.
1. Rewrote the NAVT widget code to support multi-instance NAVT widgets. Any number of NAVT widgets can now be created.
1. Localization updates
1. Fixed sign-in/sign-out urls for Word Press installations that are not in the root directory
1. Tested against WP 2.5.1

*__1.0.21 Release Candidate__ _(April 24, 2008)_

1. renamed the Slovak localization files from 'sz' to 'sk' - sorry!


*__1.0.20 Release Candidate__ _(April 24, 2008)_

1. Addition of Slovak localization (__Thanks to Tomas Forro for his help.__]
1. All localization files updated.
1. Minor changes to 'show on page' and 'show on post' group option.
1. Tightened up code for backup and restore.
1. Fix to adding uninstall/reset to the plugin page.
1. Additional corrections to current page determination
1. xpath updates


*__1.0.19 Release Candidate__ _(April 22, 2008)_

1. Minor fix for determining the current page


*__1.0.18 Release Candidate__ _(April 22, 2008)_

1. Put private and draft assets back into the asset list. These were accidently stepped on by another fix.


*__1.0.17 Release Candidate__ _(April 18, 2008)_

Just not my day. Found and fixed a very bad problem that caused navigation groups to be completely deleted by saving a new page.
__Special thanks to Alex Leonard and Bradley Charbonneau for bringing this to my attention.__


*__1.0.16 Release Candidate__ _(April 18, 2008)_

1. Sorry, I inadvertantly made 1.0.14 and 1.0.15 incompatible with WP 2.3+ by adding the shortcode function.
Should be all better now...

__Thank-you to [GNR](http://www.indiemusic.pl/ "gnr") for the catch.__


1. Update for German localization

*__1.0.15 Release Candidate__ _(April 18, 2008)_

_Hopefully, this will be the last update for awhile... but I always think that._

1. Fix to restore routine to add permalinks if pages are restored when a navigation group is restored.
1. Update to embedded navigation list handler to use short codes.
1. Fixes for theme integration.
1. Better error handling when navigation groups are not configured.
1. Corrections for adding class or selector ids to theme integration before/after group.
1. Added several messages that will appear as HTML comments within your web page source code if a navigation group cannot be displayed on your blog. If you don't see a navigation group - check the source code for a NAVT MSG comment. The comment should indicate why the navigation group was not displayed or created. This feature can be turned on and off by checking or unchecking the new group option: "Show debug as comments in HTML source code". The debug output is off by default.


*__1.0.14 Release Candidate__ _(April 17, 2008)_

1. Update to restore routine.
1. Corrections for page trees; routine could not readily determine the current page.


*__1.0.12 Release Candidate__ _(April 12, 2008)_

1. Update to Russian translation - Dmitriy Kostromin


*__1.0.11 Release Candidate__ _(April 10, 2008)_

1. Changed readme.txt and plugin header (still trying to force update notifications to work!)


*__1.0.9 Release Candidate__ _(April 8, 2008)_

1. Changed header in readme.txt to match plugin name to fix plugin update notification.
1. Update to German localization. - Rico Neitzel


*__1.0.8 Release Candidate__ _(April 6, 2008)_

1. Added Russian localization (__thanks to Dmitriy Kostromin for his help__)
1. Correction for escaping single quotations in link/alt strings (_thanks to Troy Thompson for the heads up_)


*__1.0.7 Release Candidate__ _(April 3, 2008)_

1. Added hierarchical representations to pages and categories in the asset select lists. Child page and child category names are displayed (in the Assets Panel) with the number of dash characters that corresponds to the child's relative postion - similar to the way they are displayed by Word Press. The relative position information is only for informational purposes and is not translated to represent the item's position in a navigation group.

1. Added sorting capability to pages and category assets. Radio buttons have been added to the category and page select lists that enable you to change the sort order. Pages and categories can be sorted by 'title/name' or by 'menu order'.

1. Added localized language code (I.E, en-US, de-DE, (contents of WPLANG value) ) browser name and browser version as additional NAVT menu classes. Browser names (firefox, msie, etc) are followed by the browser version as group classes. This is useful for creating CSS classes that are targetted for use with specific languages, browsers and browser versions. The German language for example, can sometimes contain more letters in an expression or sentence than English - applying a class that targets a specific language enables you to adjust container widths to prevent word wrapping.

1. Browser class names:
* firefox
* opera
* msie
* webtv
* netpositive
* mspie (MS Pocket Internet Explorer)
* galeon
* konqueror
* icab
* omniweb
* phoenix
* firebird
* mozilla (Mozilla Alpha/Beta Versions)
* amaya
* safari
* netscape

1. Version information
* Version numbers always begin with a 'v' followed by the version number without the 'DOT' characters. Check by looking at the source code produced by the browser.


*__1.0.6a Release Candidate__ _(March 30, 2008)_

1. Corrections to the readme.txt : added screenshots.

*__1.0.6 Release Candidate__ _(March 30, 2008)_

1. Fix to text with side graphic output HTML.
1. Corrected radio button selection for user navigation item (user or default avatar, gravatar)
1. Fix to 'show on...' routines to correctly handle hide on selected pages and show on selected pages
1. Changes to navt widget and navt sbm - no html output is written if navt_getlist() returns an empty list.


*__1.0.5 Release Candidate__ _(March 29, 2008)_

1. Fix to correctly allow dashes (-) in a navigation group name.


*__1.0.4 Release Candidate__ _(March 27, 2008)_

1. Fix for missing end of anchor tag for user defined URIs


*__1.0.3 Release Candidate__ _(March 25, 2008)_

1. '#wpcontent select' height was changed in WP 2.5 RC1.1 - this caused NAVT selects to display incorrectly.
Set the NAVT css stylesheets to change the select height to auto.


*__1.0.2 Release Candidate__ _(March 23, 2008)_

1. Fixed the version information
1. Added backup your data reminder to the installation page


*__1.0.1 Release Candidate__ _(March 23, 2008)_

1. Major rewrite - Brand new interface, new options


== Frequently Asked Questions ==

= Does NAVT provide a widget or a K2 sidebar module? =

__Yes.__ 5 NAVT widgets are transparently added when you activate the NAVT Plugin. The NAVT sidebar module is also transparently added to K2SBM if you are using the K2 theme.

= How do I create horizontal menus at the top of my theme? =

NAVT enables you to use your own classes by entering the CSS class information into the group options dialogbox. The group options dialogbox is indicated by the _gear icon_ on the left side of any navigation group container. Write or obtain a CSS stylesheet that contains the classes for creating a horizontal menu (there are several sources available on the Internet). Integrate the CSS stylesheet with your theme style sheet. Note the style class names for the UL and LI tags that are used in the stylesheet and enter the names into the places provided under the group CSS options tab. __Be sure to select: Do not apply CSS classes and check the Apply the CSS information below to this navigation group__ . This will force NAVT to apply the classes you've entered to the navigation group. Add the navigation group to the theme by using a widget, use the theme integration tab or by adding the navt function call directory to your theme.

= How do I use NAVT Theme Integration? =

NAVT theme integration allows you to add a navigation group into your theme without editing the theme. It does this by using an XPATH expression and applying one of the available actions: _insert before_, _insert after_, _insert above_, etc. An XPATH statment is used to identify a specific location within your theme where you'd like to put the navigation group. XPATH expressions use a combination of CSS selector ids and CSS classes to target a specific location within your theme. For example, if you wanted to place a navigation group at a specific location in your sidebar, you would formulate the necessary XPATH expression. If your sidebar had the selector id #sidebar and you wanted a navigation group to appear at the bottom of the sidebar the XPATH expression would simply be: #sidebar and you would use the _insert bottom_ action.

CSS selector ids must begin with a __#__ symbol and classes must begin with a single __dot__. You can also target locations using expressions like ___#header div.main ul.menu__ This XPATH expression describes an unordered list with the class __menu__ that is contained within a div that has the class named __main__ that is contained with a container named __header__.

Here is a working example: to __replace the top menu in a K2 theme with a NAVT navigation group__:

Select the Theme Integration tab for the NAVT navigation group you want to use. Enter the following XPATH __#page #header ul.menu__ and use the action __Replace With__. The navigation group will replace the standard K2 horizontal menu across the top of the theme.

== Screenshots ==


1. Basic components of the NAVT administration page.
2. Items are created in the Asset Panel by clicking a item in the select box and then dragging the item to a navigation group.
3. Multiple copies of the same item can be created in the Asset Panel
4. Copies can be placed into multiple groups.
