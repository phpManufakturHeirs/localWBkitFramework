## kitFramework::TemplateTools ##

&copy; 2014 phpManufaktur by Ralf Hertsch

MIT License (MIT) - <http://www.opensource.org/licenses/MIT>

kitFramework - <https://kit2.phpmanufaktur.de>

**0.27** - 2014-09-19

* no longer use `CMS_ADMIN_PATH` and `CMS_ADMIN_URL`

**0.26** - 2014-09-14

* localization is just in progress ...
* added missing language to the `html` tag
* added missing `target` property to the nav links
* updated `@link` references
* updated info URL
* added URL for the changelog in CMS Tool

**0.25** - 2014-07-28

* changed route to access commands directly
* changed title tag for the social sharing buttons
* introduce `bootstrap_nav()` option 'connect_parent' to enable navigation level to connect with the current PAGE_ID - only fitting pages will be shown

**0.24** - 2014-06-05

* templates now prompt their path and name in HTML output
* fixed: sometimes filter destroy the brackets of `[wblinkxxx]` tags
* force UTF-8 encoding for `wyswiyg_content()`
* added microdata information to the breadcrumbs
* added missing LICENSE information

**0.23** - 2014-05-26

* introduce the maintenance mode and handling for it

**0.22** - 2014-05-23

* changed `sitelinks_navigation()` to strict mode by default
* added Country Flags for the `locale_navigation()`
* changed order of initializing components for the TemplateTools
* introduce pattern `locale_navigation()`
* added constants `CMS_MAINTENANCE`, `CMS_USER_GROUP_IDS`, `CMS_USER_GROUP_NAMES`, `CMS_USER_IS_ADMIN`

**0.21** - 2014-05-20

* added parameter `zoom[]` to kitCommand `~~ google_map ~~`
* added function `wysiwyg_section_ids()`
* added functions `get_first_header()` and `remove_first_header()`
* added function `sitelinks_navigation()`
* `bootstrap_nav()` can now also process Menu names and not only ID's

**0.20** - 2014-05-16

* added constants `PAGE_LOCALE`, `CMS_MODIFIED_BY` and `CMS_MODIFIED_WHEN` and corrected the usage of `CMS_LOCALE` for global usage
* added kitCommand `~~ google_map ~~`

**0.19** - 2014-05-15

* introduce `page_option()`
* changed `page_content()` parameters to a more flexible usage
* add kitCommand `~~ wysiwyg_content ~~`
* added missing parsing for `{SYSVAR:MEDIA_REL}` before delivering the content
* move initialize `page_sequence()` out of the CMS constructor
* added kitCommands `page_modified_by`, `page_modified_when`, `cms_modified_by` and `cms_modified_when`
* added missing unsanitize and translation of values in `initialize.php`
* added kitCommand description files

**0.18** - 2014-05-12

* added support for [imageTweak](https://kit2.phpmanufaktur.de/de/erweiterungen/imagetweak.php) to `page_content()`

**0.17** - 2014-05-11

* added function `$template['browser']->ip()`
* extend the breadcrumb() function with the $options parameter `li_before` and `li_after`
* `page_content()` now return null if an alphanumeric block does not exists
* added constant `EXTRA_FLEXCONTENT_ID`
* `breadcrumb()` can now return a flexContent article as position
* added option `indicate_parent` to enable a `nav()` navigation to set attribute `active` if a child is selected (needed if the `menu_level_max` is used)

**0.16** - 2014-05-07

* added option to change the menu_level used by the Breadcrumb function
* added switch to return only the main version number and not the full string (for simplified checks) to function `$template['browser']->version()`
* added Pattern browser.check.twig (like [Browser-Update.org](http://www.browser-update.org/))
* sample templates does no longer show the full path to the script (shortened)

**0.15** - 2014-05-04

* add `normalize.css` to the classic `head.simple.twig` and `html.simple.twig`
* added `social_sharing_buttons()`
* introduce function `page_image()` to retrieve the URL of the first image of a WYSIWYG section, NEWS, TOPICS or flexContent
* introduce new example template `tt_bootstrap_three`
* extended `<head>` support in all patterns
* massive changes in the CMS Service, added support for TOPICS, NEWS and flexContent
* Changed handling of the EXTRA constants for NEWS and TOPICS to avoid conflicts at different platforms
* The NEWS addon has a small bug (missing a global declaration for `$MOD_NEWS`) which will be now fixed by the Setup/Update
* Introduce the new Service for `Browser`

**0.14** - 2014-04-27

* added constant `PAGE_HAS_CHILD` and function `$template['cms']->page_has_child()`
* added `Breadcrumb` and `Nav` Navigation for Bootstrap
* added `$template['bootstrap']->alert()` function
* added template examples `tt_classic_one`, `tt_classic_two`, `tt_bootstrap_one` and `tt_bootstrap_two` - the examples will be installed as Templates in the CMS
* add `twig` function `file_exists()`
* added many patterns and completed the [WIKI for the TemplateTools](https://github.com/phpManufaktur/kfTemplateTools/wiki)  

**0.13** - 2014-04-16

* fixed: constant `PARENT` is not available in BlackCat CMS

**0.12** - 2014-04-16

* fixed: constant `LEVEL` is not available in BlackCat CMS

**0.11** - 2014-04-16

* fixed: define `CMS_USER_ACCOUNT_URL`, `CMS_LOGIN_URL`, `CMS_LOGIN_FORGOTTEN_URL`, `CMS_LOGIN_SIGNUP_URL`, `CMS_LOGOUT_URL`
* fixed #1: ErrorException: Warning: mysql_fetch_array() 

**0.10** - 2014-04-15

* initial release
