== Pressidium Cookie Consent ==
Author URI: https://pressidium.com/
Plugin URI: https://github.com/pressidium/pressidium-cookie-consent/
Contributors: pressidium, overengineer
Tags: cookie, consent, gdpr, ccpa, cookies
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable Tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Lightweight, user-friendly and customizable cookie consent banner to help you comply with the EU GDPR cookie law and CCPA regulations.

== Description ==

Pressidium Cookie Consent makes it easy to add a stylish, customizable cookie consent banner to your website and conditionally load third-party scripts (analytics, performance, targeting, etc.) based on the user-selected preferences to help you comply with EU GDPR cookie law, CCPA, and similar regulations.

= ⚙️ Fully customizable =

Easily modify the cookie consent banner and settings modal text. Pick one of the built-in color presets to match your website’s aesthetic, or adjust the color of individual components via our convenient color picker. Choose from a variety of layouts, positions, and transitions directly from the WordPress dashboard.

= 🪶 Lightweight =

The plugin is built on top of the Cookie Consent JS library by Orest Bida, a standalone vanilla JavaScript library with no third-party dependencies, that loads blazingly fast and won’t slow down your website.

= ♿ Accessible =

An a11y friendly plugin — fully accessible and WAI-ARIA compliant.

= 🌐 Translatable =

Modify any text in the cookie consent banner and settings modal, and provide translations. Choose the language auto-detection strategy you prefer — either  read the user’s browser language or read the markup of the current page to identify its primary language.

= 📱 Responsive =

Fully responsive and mobile-friendly cookie consent banner and settings modal.

= 🙅 Block scripts =

Control which scripts are loaded based on the user’s preferences. List the URLs of the scripts you’d like to block (both exact matches and regular expressions are supported) and prevent them from running until the user has given consent.

= 🧹 Auto-clear cookies =

Automatically delete cookies when a user opts-out of a specific category inside cookie settings.

= ✋ Force consent =

Block the user from interacting with the page until they consent to your cookie policy.

= 🔁 Re-consent =

Ask users to consent again when you update your cookie policy.

= 📝 Consent records =

Record user consents to be able to provide proof of consent for auditing purposes.

= 🤖 Hide from bots =

Automatically parse the user agent to detect bots, crawlers, and webdrivers. If one is detected, you have the option to hide the cookie consent banner from them.

= 🧩 Google Consent Mode (GCM) integration =

Integrate with Google Consent Mode (GCM) to adjust how Google tags behave based on the user’s consent preferences. Supports both Google tag (gtag.js) and Google Tag Manager (GTM) implementations.

= ⌨️ Control it programmatically =

Programmatically control the plugin. Conditionally show/hide the cookie consent banner and/or the settings modal, accept one (or more) cookie categories, erase cookies, load previously blocked scripts, etc.

== Installation ==

= Automatic installation =

Automatic installation is the easiest option — WordPress will handle the file transfer, and you won’t need to leave your web browser.

1. Log in to your WordPress dashboard
2. Navigate to the “Plugins” menu
3. Search for “Pressidium Cookie Consent”
4. Click “Install Now” and WordPress will take it from there
5. Activate the plugin through the “Plugins” menu on WordPress

= Manual installation =

1. Upload the entire `pressidium-cookie-consent` folder to the `wp-content/plugins/` directory
2. Activate the plugin through the “Plugins” menu on WordPress

= After activation =

1. Go to the plugin settings page (Settings -> Cookie Consent)
2. Customize the cookie consent to your liking
3. Click “Save” to save your changes

== Frequently Asked Questions ==

= Is this plugin free? =

Yes! This plugin is 100% free and open source.

= Will this plugin make my website GDPR/CCPA compliant? =

Yes, the plugin will help you be GDPR and CCPA compliant if you set it right.

= How do I customize the cookie consent banner? =

You can customize the cookie consent banner by going to the plugin settings page. On wp-admin, go to Settings -> Cookie Consent.

= How do I add a cookie consent banner to my website? =

Just install and activate the plugin. The cookie consent banner will automatically be added to your website. You can customize the banner by going to the plugin settings page.

= Does the plugin automatically scan my website to list the cookies it stores? =

No, the plugin doesn’t scan your website. You will have to manually list the cookies you use. On wp-admin, go to Settings -> Cookie Consent, select the “Cookies” tab and list all Analytics and Targeting cookies.

= Does the plugin automatically block third-party scripts? =

No, the plugin doesn’t automatically block third-party scripts. You will have to manually list the URLs of the scripts you want to block. On wp-admin, go to Settings -> Cookie Consent, select the “Block Scripts” tab and list all third-party scripts you want to block (regular expressions supported).

= Can I integrate this plugin with my WordPress theme/plugin to block any scripts it loads? =

Yes. On wp-admin, go to Settings -> Cookie Consent, select the “General” tab and make sure the “Page Scripts” option is enabled. Then, set the `type` of your scripts tags to `"text/plain"` and set a `data-cookiecategory` attribute to `"analytics"`, `"targeting"` or `"preferences"`.

For example, `<script type="text/plain" data-cookiecategory="analytics" src="analytics.js" defer></script>`

For more information, refer to the [“Blocking scripts” section of our wiki](https://github.com/pressidium/pressidium-cookie-consent/wiki/).

= Can I use the cookie consent plugin programmatically? Are there any other options/features? =

For more information about the Pressidium Cookie Consent plugin, refer [to our wiki](https://github.com/pressidium/pressidium-cookie-consent/wiki/).

= Can I export/import the plugin settings? =

Yes, you can export/import the plugin settings. On wp-admin, go to Settings -> Cookie Consent, and use the “Export Settings” and “Import Settings” buttons.

= Does the plugin support Google Consent Mode (GCM)? =

Yes, the plugin supports Google Consent Mode (GCM). On wp-admin, go to Settings -> Cookie Consent, select the “Consent Mode” tab and enable the “Google Consent Mode v2 (GCM)” option. Then, select either the Google tag (gtag.js) or the Google Tag Manager (GTM) implementation, and configure it accordingly.

= Why is the cookie consent banner not showing on my website? =

Make sure that either the “Autorun” option is enabled on the plugin settings page, or that you manually call the `pressidiumCookieConsent.show()` method.

= Why are my changes not getting saved? =

Make sure you have clicked the “Save” button on the plugin settings page.

= Where can I report any bugs and/or request additional features? =

If you have spotted any bugs, or would like to request additional features from the plugin, please [file an issue](https://github.com/pressidium/pressidium-cookie-consent/issues/).

== Screenshots ==

1. General settings
2. Themes and colors
3. Cookie tables
4. Translations
5. Consent modal
6. Settings modal
7. Blocked scripts
8. Consent records

== Changelog ==

= 1.3.0: Nov 30, 2023 =

* Fix an issue where an empty source for a blocked script would cause some scripts to not be loaded
* Remove debugging logs from the console
* Add options to set a different color for the text when a button is hovered

= 1.2.2: Nov 29, 2023 =

* Wrap Composer dependencies in our own namespace to prevent conflicts with other plugins loading the same dependencies in different versions

= 1.2.1: Nov 6, 2023 =

* Check if the table already exists in the database before attempting to create it

= 1.2.0: Oct 2, 2023 =

* Add a new Consent Records tab to the settings page to be able to provide proof of consent for auditing purposes

= 1.1.6: Aug 23, 2023 =

* Fix an issue where languages with a hyphen in their code were not being detected correctly

= 1.1.5: Aug 22, 2023 =

* Improve multilingual support

= 1.1.4: Aug 21, 2023 =

* Fix an issue where the confirmation before leaving the tab was being triggered even when there were no unsaved changes

= 1.1.3: Aug 4, 2023 =

* Fix an issue where the “Cookie table headers” setting was not being saved correctly

= 1.1.2: Jul 27, 2023 =

* Add the ability to list necessary cookies
* Fix emoji decoding to render correctly in text fields and textareas

= 1.1.1: Jul 25, 2023 =

* Fix an issue where the log file could not be created

= 1.1.0: Jul 25, 2023 =

* Add the ability to export, import, and reset settings
* Add POT file for localization
* Ask for feedback on plugin deactivation
* Support emoji even on databases using the `utf8` character set
* Improve logging
* Add a new Logs tab to the settings page to help with debugging

= 1.0.4: Jul 2, 2023 =

* Fix a conflict with plugins that use `null` as their admin footer text

= 1.0.3: Jun 30, 2023 =

* Only load blocking script when needed

= 1.0.2: Jun 27, 2023 =

* Fix an issue where the text of the secondary button could not be updated

= 1.0.1: Jun 19, 2023 =

* Update Plugin Directory icons

= 1.0.0: May 26, 2023 =

* Initial version
