## kitFramework::kfBasic ##

(c) 2013 phpManufaktur by Ralf Hertsch

MIT License (MIT) - <http://www.opensource.org/licenses/MIT>

kitFramework - <https://kit2.phpmanufaktur.de>

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