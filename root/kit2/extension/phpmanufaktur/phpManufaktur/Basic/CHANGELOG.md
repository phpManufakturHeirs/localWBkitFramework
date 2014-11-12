## kitFramework::kfBasic ##

(c) 2013 phpManufaktur by Ralf Hertsch

MIT License (MIT) - <http://www.opensource.org/licenses/MIT>

kitFramework - <https://kit2.phpmanufaktur.de>

**1.0.17** - 2014-11-03 - first release by webbird

* new utility method `getToolbar()`
* new helper for populating and validating forms
* some form tweaking

**1.0.16** - 2014-09-22

* bugfix: get attribute to calculate the iframe height use the wrong identifier
* the kitCommand parser is now checking for parameter `load_css[]`
* fixed problems during first setup of BASIC

**1.0.15** - 2014-09-18

* added CMS Tool `About` dialog
* fixed setting the iFrame height for the CMS Tool
* introduce a migration tool
* `CMS_ADMIN_PATH` and `CMS_ADMIN_URL` are no longer supported
* removed `CMS_TEMP_PATH` and `CMS_TEMP_URL` (will be created dynamically)
* `CMS_PATH` is no longer needed in `/config/cms.json`
* added `FRAMEWORK_UID` to the test mail and to the about dialog in CMS Tool
* added `SERVER_EMAIL_ADDRESS` and `SERVER_EMAIL_NAME` to the Twig constants

**1.0.14** - 2014-09-14

* missing important update execution for the CMS addon ...

**1.0.13** - 2014-09-14

* fixed invalid usage of namespace in register extensions
* add missing update of register after processing extension files

**1.0.12** - 2014-09-14

* checked and updated all `@link` references
* added check and view for Changelog in kitFramework CMS Tool
* added missing hints for available catalog or updates at sub dialogs
* added route to truncate the i18n tables and add an entry point for it
* finished updater reworking and prepare updates for the kitFramework itself

**1.0.11** - 2014-09-13

* rework the CMS Tool - experimental release.

**1.0.10** - 2014-09-05

* add missing default locale definition in `iframe.body.twig`
* catch problem creating new images - now return empty image with a hint instead of throwing an error
* set missing translator locale in first login dialog
* fixed: Session variables must be set before the first login dialog to enable a proper locale setting
* if `<title>` tag is missing in CMS template the kitCommand Parser now insert this tag
* translation still in progress

**1.0.9** - 2014-08-28

* added function `getSetValues()` to `$app['db.utils']`
* exclude all `/Include` directories from i18n Parser to avoid overhead
* changed formatting and styling for usage of extensions as CMS Tool: grant a padding of 5px and always a white background for the tools.
* added custom unassigned translations to the i18nEditor
* added `togglePageTree()` to enable Admin-Tools to toggle the page tree in BlackCat installations
* extended and improved handling of 403, 404, 405, 410 and 423 HTTP errors
* changed the translation service initialization and the setting of the default locale
* smaller changes in formatting and translations

**1.0.8** - 2014-08-13

* fixed: BASIC needs the CKEditor also at the first initialization, added dependency

**1.0.6** - 2014-08-11

* fixed some problems with the i18nEditor

**1.0.5** - 2014-08-11

* Introduce the i18nEditor
* generate extended debug information if the form submission is not valid
* fixed: ConfigurationEditor, problem shortening paths for the selection list in a correct and expected way
* fixed: strong typo may cause problems with the output filter - PAGE_ID will not recognized

**1.0.4** - 2014-07-28

* Parser: improved search for locking characters in kitCommands like `&nbsp;`
add monolog debug information for the parsing process
* Outputfilter: improved detection of PAGE_ID in CMS

**1.0.3** - 2014-07-27

* bugfix: embedded kitCommands can also return a JSON response which was not handled

**1.0.2** - 2014-07-26

* bugfix: parser must prevent to execute the command as filter if the parameter `help[]` isset
* added functions `hex2rgb()` and `rgb2hex()` in `$app['utils']`
* addded more exclude filters for *.json files in the jsonEditor
* missing sanitizing path's to make the jsonEditor proper working at windows ...

**1.0.1** - 2014-07-22

* missed update for the changed `config.jsoneditor.json`

**1.0.0** - 2014-07-22

* BASIC has reached **1.0.0** and is a stable release!
* changed the handling of the help texts - extensions can now "inject" the hints into the `config.jsoneditor.json`
* added route `/admin/json/editor/open/file/{filename}` to enable direct loading of specified config files

**0.99** - 2014-07-21

* Introduce the kitFramework Configuration Editor

**0.98** - 2014-07-16

* extended check for GUID to prevent problem if user want to get a new password at the first login
* add cURL handling to BASIC `PermanentLinkBase` for usage with extensions
* add check at LOGIN if account is LOCKED (was missing)
* add information at forgotten password dialog if the account is locked

**0.97** - 2014-06-30

* reworked the `simulate` kitCommand
* Setting a canonical link must also update an existing meta tag `og:url` !
* Update delete no longer needed `/Basic/Control/kitCommand/kitCommand.php`
* add Twig namespaces for @pattern and @template if the TemplateTools are in use
* add function `truncateTable()` to `$app['db.utils']`
* enable general posting of alerts for kitCommands
* add function `getEMailAddress()` to `$app['account']`
* extended check for entry points - user who has no access to entry points will be now redirected to his user account
* extended the password forgotten / create twig templates so they can used also with kitCommands
* add function `isFunctionDisabled()` and check for `set_time_limit()` to `$app['utils']`
* introduce `promptAlertFramework()` for class `Alert`

**0.96** - 2014-06-11

* fixed a problem executing the Alert box as standalone page
* extended check for page.cache, fixed content language
* changed bootstrap and font-awesome loading in backend `body.twig` to `/latest` release
* changed library dependencies to `/latest`
* added kitCommand `~~ guid ~~`
* introduce function `isJSON()` in `$app['utils']`
* added svg graphic for indication empty images to the template `pattern`
* start complete redesign and rewriting of the CMS OutputFilter
* rewriting the kitCommand names enable to separate `simulate[]` to a own kitCommand and remove from OutputFilter
* changed parsing method for kitCommands from inside kitFramework extensions
* introduce kitCommand Parser, remove the routes to `/kit_command/` 
* introduce virtual kitCommand `simulate`
* changed kitFramework meta generator information

**0.95** - 2014-06-03

* fixed a strong typo in OutputFilter
* Try to create a symlink in /media/public to the CMS /media directory
* fixed: sometimes the outputfilter destroy the brackets of the [WBLINKxx] tags
* changed loading for bootstrap and font-awesome to `latest`
* changed `iframe.body.twig` to container-fluid
* changed ROBOTS handling for kitCommands `catalog`, `help` and `list`

**0.94** - 2014-05-26

* introduce the constant `FRAMEWORK_UID` with a unique ID for each kitFramework installation which can be used for simplified authentication of application processes

**0.93** - 2014-05-23

* extended checks for the DOM parser to prevent from accessing uninitialized Objects in CMS Outputfilter
* add detection for GET parameter `robots`  for SEO improvements (*important!*)

**0.92** - 2014-05-01

* bugfix for a problem loading the locale files by BASIC (stumbled if missing a third party extension)

**0.91** - 2014-04-30

* BASIC is now loading *all* locale files, the function `$app['utils']->addLanguageFiles()` is no longer needed and marked as deprecated

**0.90** - 2014-04-28

* solved problem with exceeded download limit at Github
* added check to prevent access to non-initialized DOM Object in Output Filter

**0.89** - 2014-04-16

* Bugfix: extended check for `TOPIC_ID` and `POST_ID` in OutputFilter

**0.88** - 2014-04-15

* fixed path for the CMS Authenticate classes
* added CMS_PATH also for the Twig Templates
* fixed a problem in language dependency
* fixed a strong typo: used `topic_id` instead of `post_id`
* Changed the markup parser handling for the twig template engine
* moved month- and week names to /Metric/de.php
* extended check for POST_ID and TOPIC_ID

**0.87** - 2014-03-24

* added `getPageVisibilityByPageID()`
* the OutputFilter now also return the CMS page visibility (public, hidden, private, registered or none)
* BASIC set session var `CMS_USERNAME` if the CMS user is authenticated
* removed manual session start from `bootstrap.include.php`
* added CMS Authentication function

**0.86** - 2014-03-18

* **SEO**: added block for `canonical` links to the kitCommand `iframe.body.twig` variants

**0.85** - 2014-03-17

* added weekday and month names translations

**0.84** - 2014-03-10

* changed account handling in the custom `AuthenticationSuccessHandler`
* add a email test via `http://<yourdomain.tld>/kit2/admin/test/mail`
* introduce daily rotate of framework logfile instead of rotating by size
* update remove the old kit2 logfiles
* bugfix: function `getURLbyPageID()` must be public!
* fixed a hard typo which causes problems at directly login in kitFramework

**0.83** - 2014-02-24

* improved the output filter to avoid duplicate loading of CSS and JS files

**0.82** - 2014-02-16

* added function `humanize()` to global class `Utils`

**0.81** - 2014-02-12

* improved performance for search in kitCommands

**0.80** - 2014-02-11

* fixed dependencies while first initialization, download and configure missing `Library`

**0.79** - 2014-02-10

* added missing dependency for the library
* changed Updater to extend class `Alert` 
* in class `Basic` the message functions are now marked as deprecated
* fixed a small bug in kitCommand `list`
* changed kitFramework iFrame body to Bootstrap 3.1.0

**0.78** - 2014-02-06

* no further support for the additional /white template!
* added CSS Formatting to the kitCommand Parameter `simulate[]` Replacement
* Added support for canonical links, use parameter `canonical[]` in kitCommands

**0.77** - 2014-02-03

* added missing route for `/admin` (without ending slash)
* add the general kitCommand parameter `simulate[]` to avoid the execution of the kitCommand and to show the command expression, without the parameter `simulate[]`
* fixed a routing typo for `isFilterAvailable()`
* now every extension update clear the Twig Cache Files at finishing the installation/update of extensions
* automatically remove kitCommand Parameter entries (PID) which are older than 48 hours
* add debug info if the Twig Cache is cleared by a kitCommand with parameter `cache[0]`
* added `setMetaTag()` to the OutputFilter to process meta tags i.e. for robots or generator
* The library is moved to the separate kfLibrary - removed the old files from the BASIC extension

**0.76** - 2014-01-31

* added functions for use in the CMS: `kitFramework_isInstalled()`, `kitFramework_isCommandAvailable()`, `kitFramework_isFilterAvailable()`

**0.75** - 2014-01-29

* switch back to usage of exact version to load libraries instead of using 'latest' - causes to many problems

**0.74** - 2014-01-24

* fixed counting of role entry points
* added missing check if `PAGE_ID` is defined in parent CMS
* changed handling to get the TOPICS directory - no longer try to include the `module_settings.php`
* changed paths for Bootstrap and Font-Awesome from fixed version to 'latest'

**0.73** - 2014-01-22 

* fixed checking for entry points
* added cleanup for Release 0.72

**0.72** - 2014-01-22

* moved all Account classes from /control to /control/account
* add `getUserRolesEntryPoints()` to class `account`
* added dropdown menu with all for the user available entry points to the admin and user backend
* added handling for the entry points
* implemented user account dialog
* added admin account control and handling functions: view, create, edit, update and delete - send account information to user etc.

**0.71** - 2014-01-16

* changed handling for a missing parameter ID
* prevent problems with usage of `http_build_query()` if SERVER has not set  `arg-separator.output`

**0.70** - 2014-01-13

* changed check for and loading of `HTMLPurifier`
* added `getTopicsDirectory()` to the CMS functions
* changed `DOM` handling within the `Ellipsis()` function
* added parameter `$order_by` and `$oder_direction` to `getPageLinkList()`

**0.69** - 2014-01-10

* add CMS user `select()` and some missing comments
* added `IMAGE` `getSupportedImageTypes()`
* added `IMAGE getMimeType()`
* Moved ellipsis function from `Twig` to `UTILS`
* added handling for HTML formatted ellipsis (need `LIBRARY htmlpurifier`)
* add CMS function `getPageLinkByPageID()`
* introduce namespace autoloading by the new `LIBRARY` - setup/update will change the kitFramework autoloader to enable this feature

**0.68** - 2014-01-06

* add `ellipsis` filter to Twig 
* the output filter can now set header information (title, description, keywords) for a kitCommand - need GET or parameter `set_header` with a ID, calls `kit2/command/{kitcommand}/getheader/id/{id}` to get JSON with header information
* class Alert can now directly prompt Alerts (using Bootstrap 3 iframe) setAlert() can now retrieve additional debug information for logging
* added `getPageIDbyPageLink()` and `existsCommandAtPageID()`
* add `getPageLanguage()`
* added Twig function `image()` which return the image dimension and can re-sample the given image (imageTweak base function)
* add general kitCommand parameter `library[]` which enable to load `.js` and `.css` files from the BASIC library
* changed the handling for the CMS search filter
* kitCommands and kitFilter now always log and report occuring errors via monolog and email (if configured)
* changed the JSON handling for the search function
* share the Image class systemwide
* added class(es) to get the CMS system settings

**0.67** - 2013-12-27

* added missing jQuery *.map files to the library
* add Twig function `fileExists()`
* add Twig global array `FRAMEWORK_TEMPLATES`
* Introduce class `Alert` as Basic pattern (Bootstrap 3)
* added bootstrap pattern for formatted groups of checkboxes and radiobuttons

**0.66** - 2013-12-19

* added missing support for `page_title[]` in white layout
* added missing charset definition in UTF-8 support

**0.65** - 2013-12-19

* added general kitCommand parameter `page_title[]`

**0.64** - 2013-12-17

* fixed a bug installing Admin-Tools in the CMS

**0.63** - 2013-12-17

* add isTracking() and disableTracking()
* introduce LIBRARY
* added jQuery 1.10.2
* added jQuery 2.0.3
* added jQuery Migrate 1.2.1
* added jQuery UI 1.10.3
* added jQuery TagEdit 1.5.1
* added jQuery Timepicker 1.4.3
* added Bootstrap 2.3.2
* added Bootstrap 3.0.3
* added Font Awesome 3.2.1
* added Font Awesome 4.0.3
* changed BASIC backend and kitFramework catalog to responsive Bootstrap 3

**0.62** - 2013-12-01

* added GET handling for the parameter frame_scroll_to_id
* added check if the given frame_scroll_to_id exists
* enable to name the extension logo type - by default 'jpg' will be choosen
* improved setting of the kitCommand iFrame auto height: get the ID of the frame dynamically to prevent problems if the browser go back button is used and a new iFrame ID was generated
* introduce the kitCommand `catalog` to show the kitFramework Catalog with detailed information

**0.61** - 2013-11-27

* introduce class `dbUtils` and share it as `$app[db.utils]`
* added missing check to `bootstrap.php` for MySQL InnoDB

**0.60** - 2013-11-19

* set the `CMS_PATH` from `FRAMEWORK_PATH` to avoid invalid assignments at mobile installations
* introduce `/pattern` templates and add a pattern for the about dialog
* BASIC get the parameter ID (PID) now also from submitted `form.factory` forms
* changed visibility for $message to protected to enable extended classes to set messages direct without auto-formatting
* switch off the debugging mode if the application return a 403 HTTP error
* added new kitCommand parameter `cache[]`
* add `createGUID()` and `getUserByGUID()` to class `Account`
* improved the `CustomLogoutSuccessHandler` and assigned actions/forms
* added missing `guid_status` at `createNewGUID()` to class `Users`
* fixed a incomplete check for the license key at setup of Admin-Tools 

**0.59** - 2013-11-09

* changed license information and handling in `extension.json`
* removed link to german help file for the kitCommands because it is incomplete, use the english help instead
* added `getProxyInfo()` in class `Utils` - return array with PROXY information
* fixed: `_recaptcha_http_post()` support now also the usage of a PROXY server

**0.58** - 2013-11-08

* set FRAMEWORK_PATH from BOOTSTRAP_PATH and get it no longer from the framework.json (needed for mobile Installations)

**0.57** - 2013-11-07

* fixed wrong access to license name in class `InstallAdminTool`
* cURL timeout in OutputFilter was too strong, changed to 30 seconds

**0.56** - 2013-11-06

* bugfix: kitFilter handle the CMS parameter string incorrect 

**0.55** - 2013-11-04

* extended CMS check, add '-unknown-' if check fail
* extended `extension.json` with additional vendor information, added help and support links
* added support for icon of the extension in the CMS backend (WebsiteBaker 2.8.4 and BlackCat)

**0.54** - 2013-10-30

* separate the METRICS from the general language files
* fixed invalid check if *reCaptcha* is enabled
* the new null.twig template can be used to hide a `iframe` by setting the height to zero pixel
* bugfix: changed check for `proxy.json` to avoid problems with a opendir restriction
* fixed: missed information about CMS type and version in the parameter array
* improved assigning the preferred browser language to the BASIC class
* Added a `CustomLogoutSuccessHandler` - enable a own Goodbye dialog, setting messages and redirecting to other routes after successfull logout
* Added `CustomAuthenticationSuccessHandler` and tracking of the login for all users
* add login tracking
* new class `AdminAction` enable actions to change user roles with email commands a.s.o
* improved checkLogin()

**0.53** - 2013-10-24

* small but important change of the default date and time formatting

**0.52** - 2013-10-20

* added `parseFileForConstants()` to `$app['utils']` 

**0.51** - 2013-10-18

* fixed: on update the install process for the search function may cause an exception

**0.50** - 2013-10-18

* extended definition check with `SYNCDATA_PATH` in OutputFilter
* added utf8_entities() as solution for the MySQL client Latin1 -> UTF-8 problem 

**0.49** - 2013-10-14

* critical: fixed wrong path for output filter in LEPTON installations

**0.48** - 2013-10-14

* fixed: app['utils'] uses wrong namespace for the output filter
* add additional check for CMS_PATH to see if autoloading is enabled
* fixed: sometimes the kitCommand BASIC class does load the 'default' template instead of the preferred template from the framework.json
* fixed incorrect target for external call of the kitFramework
* introduce standard template 'white', the 'default' template is changed to transparent backgrounds

**0.47** - 2013-10-11

* moved the handling of the kitFramework Search Section from CMS Tool to BASIC
* changed handling of monolog logger
* changed handling of class UnZip

**0.45** - 2013-10-07

* fixed a foolish output of the the search filter ... 8-)
* suppress the execution of kitCommands in the CMS search results 

**0.44** - 2013-10-06

* fixed problems with detecting previous versions and setting namespace for LEPTON output_interface

**0.43** - 2013-10-06

* fixed a problem with setup archive file

**0.42** - 2013-10-06

* Moved OutputFilter from \Basic\Control\kitCommand\OutputFilter to \Basic\Control\CMS and added compatibillity classes for the different CMS
* added and activated the search function for the kitCommands
* added search filter for NEWS and TOPICS
* add check if a `kit_framework_search` section exists in the CMS

**0.41** - 2013-10-04

* added CURRENCY_SYMBOL to the language files

**0.40** - 2013-10-01

* added getPageTitle() to CMS functions
* fixed a problem getting the URL of NEWS Posts
* added missing check for CATALOG_ACCEPT_EXTENSION

**0.39** - 2013-09-25

* changed handling for download of redirected Github repositories

**0.38** - 2013-09-25

* fixed: the post parameter from the output filter may be disturbed, changed the handling
* changed: handling for adding extra space to kitCommand iFrames 

**0.37** - 2013-09-24

* fixed: checking remote IP always return 127.0.0.1

**0.36** - 2013-09-22

* improved the handling for installation and upgrading extensions

**0.34** - 2013-09-20

* changed template handling 
* changed authentication handling 

**0.33** - 2013-09-16

* added ReCaptcha handling and Twig extension
* Introduce kitFilter and add as first filter MailHide with ReCaptcha
* Changed behaviour of the welcome dialog: only admins can access, at first access the user must login to create a kitFramework account and enable auto-login for the future access.
* added path variables to the Twig extension
* changed minimun height for kitCommand iFrames to 5px
* changed static iframe ID to dynamically created IDs - this enable multiple kitCommand iFrames at the same WYSIWYG page

**0.32** - 2013-09-12

* looks like 'BETA' is coming soon ... 8-)

**0.31** - 2013-08-26

* just in progress ...

**0.28** - 2013-08-07

* changed handling of initParameters() in the kitCommand Basic class

**0.27** - 2013-08-07

* changed internal handling for kitCommands
* controllers can now use classes
* fixed a problem with proxies

**0.26** - 2013-08-06

* added support for installations behind a proxy
* restructured the template directory
* removed no longer used code

**0.25** - 2013-08-02

* added support for BlackCat CMS

**0.24** - 2013-08-01

* added the EmbeddedAdministration feature
* switched cURL SSL check off

**0.23** - 2013-07-25

* first beta release

**0.10** - 2013-04-05

* initial release