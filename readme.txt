=== Misamee Gravity Forms Themes ===
Contributors: sciamannikoo
Donate link: http://misamee.com/donate/  
Tags: gravity forms, gravity-forms, shortcode, theme, customization, template
Requires at least: 3.3  
Tested up to: 3.4  
Stable tag: 1.3
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  

Add themes (skins) to Gravity Forms

== Description ==

Misamee Gravity Forms Themes provide a couple of skins for your Gravity Forms and allows you to create your custom skins.
Skins can include just a CSS, but also one or more JavaScript and php files.

You also get a customized version of the standard Gravity Forms widget that allows you to select a theme.

* [More details](http://misamee.com/2012/11/themed-gravity-forms-examples/)

== Installation ==

= Easy way =

Add the plugin from your WordPress site and activate, or...  

= Geek way =

1. Download `misamee-gravity-forms-themes.zip`
2. Decompress `misamee-gravity-forms-themes.zip` in a directory called `misamee-gravity-forms-themes`
3. Upload the whole directory (not just its contents) to the `/wp-content/plugins/` directory
4. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently asked questions ==

http://misamee.com/2012/11/themed-gravity-forms-examples/

== Screenshots ==

Coming soon

== Changelog ==

= 1.3 =
* [Themes bug] Show labels for checkboxes and radio buttons
* [Theme improvement] Default Theme javascript file scope changed to '.themed_form' (common on all themed forms) in order to be reused by other themes
* [Theme improvement] FormSpring javascript file removed as is identical to the default theme javascript (see updated functions.php)
* [Theme improvement] Autumn javascript file removed as unused anyway

= 1.2 =
* Now you can add a hidden field in your forms to specify which theme should be used: add a field called misamee-theme and set the default value to the theme you want to use

= 1.1 =
Improved custom script/js/php handling (functions.php in new themes must be updated: see included themes).

= 1.0 =  
* First release

== Upgrade Notice ==

= 1.3 =
Checkboxes and Radio buttons labels now are shown on default and formSpring themes.

= 1.2 =
A new way to set the theme.

= 1.1 =
There was a wrong handling of custom files.
Please see included themes, especially the functions.php file: if you have created your own themes, you must update them!

= 1.0 =  
First release