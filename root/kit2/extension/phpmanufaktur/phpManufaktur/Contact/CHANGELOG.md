## Contact for kitFramework ##

(c) 2010, 2014 phpManufaktur by Ralf Hertsch

MIT License (MIT) - <http://www.opensource.org/licenses/MIT>

kitFramework - <https://kit2.phpmanufaktur.de>

**2.0.42** - 2014-09-14

* fixed wrong release date
* changed toolbar layout
* changed source for translation
* replaced usage of `ucfirst()` with `$app['utils']->humanize()`
* small fix for `|humanize` filter
* updated `@link` references
* updated info URL
* added URL for the changelog in the CMS Tool

**2.0.41** - 2014-09-05

* fixed: `category_type_target_url` can not be NULL
* improved translation

**2.0.40** - 2014-08-28

* the `PRIVATE` flag is no longer in use, fixed multiple files
* added filter `|humanize` for person gender
* add form field formatting for `attr.label_column` and `attr.widget_column`
* fixed a strong typo - using 'contact' instead of 'category' in contact pattern
* added `parseZIP()` to the contact interface and implement it in the contact pattern
* improved translation and formatting

**2.0.39** - 2014-08-11

* improved translation handling, added support for i18nEditor
* generate extended debug information if the form submission is not valid 
* buxfix: `import_xlsx` is no longer used

**2.0.38** - 2014-07-16

* centralized usage of `form.fields.horizontal.twig`
* bugfix: used `trim()` in write context in `ContactSearch`
* bugfix: missing `WHERE` in SQL statement in `Category`
* introduce extra field type `SELECT_TABLE`
* introduce special fields in contact form
* bugfix: public search does not look for ZIPs
* simplified usage of the standard dialogs and templates
* changed address and communication usage types - removed PRIVATE and BUSINESS, use PRIMARY instead
* changed route for admin access to contact
* finished reworking of the main contact dialog for all extensions
* `action[list]` and `action[search]` can now handle a "go back" if a contact detail is viewed
* changed Excel import handling
* PermanentLink is now using BASIC PermanentLinkBase
* add function `getContactID()` to interface
* add check if GUID is expired
* add check for form attributes `translate`, `columns` and `config`

**2.0.37** - 2014-07-02

* introduce search function for contacts with public access, just use the kitCommand `~~ command action[search] ~~`
* bugfix: config.contact.json was missing the settings for the register dialog
* improved and speed up some SQL queries

**2.0.36** - 2014-06-30

* changed handling of `container` and `container-fluid`
* bugfix: used unsanitize instead of sanitize at category type insert
* introduce kitCommand `~~ contact action[list] ~~`
* extend contact for the field `category_type_access` which enable classifying contacts as `PUBLIC` or `ADMIN`
* introduce the contact filter for the usage with the kitCommands
* introduce kitCommand `~~ command action[view] ~~`
* introduce kitCommand `~~ contact action[register] ~~`
* introduce `libphonenumber` for the phone number check and formatting
* introduce export of contact records as `csv` or `xlsx` file
* introduce import of contact records in `csv`, `xls`, `xlsx` and `ods` file format

**2.0.35** - 2014-05-07

* all language files are now loaded by the BASIC extension
* added functions `searchContact()` and  `addMessage()` to `$app['contact']`

**2.0.34** - 2014-03-18

* added form `sample_newsletter` to show Newsletter Subscriptions

**2.0.33** - 2014-02-24

* added `selectContactIdentifiersForSelect()` to `$app['contact']`

**2.0.32** - 2014-02-16

* added `FORMS` for `Contact`
* introduce the kitCommand `~~ contact ~~` 

**2.0.31** - 2014-01-22

* fixed a path to `bootstrap.min.js`
* added parameter `usage` to all 'simple' templates to avoid problems with global handling/embedding
* add security role hierarchy and security entry points 

**2.0.30** - 2013-12-27

* changed the Contact Backend to Bootstrap 3 

**2.0.29** - 2013-12-20

* improved import from previous KeepInTouch installations

**2.0.28**

* enable to update also records with status `DELETED`

**2.0.27** - 2013-12-19

* add `icon.png` and `tool_icon.png` for BlackCat CMS and WebsiteBaker 2.8.4 support
* added `getPrimaryEMailAddress()` to Contact Control
* share Contact Control as $app['contact']

**2.0.26** - 2013-11-27

* added `getStatus()` to Contact Control
* added `existsCategory()` and `createCategory()` to class ContactCategory
* Contact Control: `select()` handle now also the login name instead of the contact ID
* Contact Control: `insert()` handle now also submitting extra fields
* Contact Control: added `existsCategory()`, `createCategory()`, `existsExtraTypeName()`, `createExtraType()`, `bindExtraTypeToCategory()`
* extra fields are now automatically inserted and deleted for all assigned contacts
* fixed: category edit calls the wrong route

**2.0.25** - 2013-11-10

* changed to Font Awesome 4.0.3 and make it local available
* make Contact available as Admin-Tool in the CMS backend
* added `create` routes to the simple dialogs

**2.0.24** - 2013-11-08

* check for `contact_since` at inserting a new record fails because key was quoted ... 8-/
* prevent field `extra_type_description` from being NULL at creating a new extra field

**2.0.23** - 2013-11-04

* added support for additional vendor information

**2.0.22** - 2013-10-30

* introduce configuration file `config.contact.json`
* added preferred choices for countries - get data from `config.contact.json`
* can configure if the email field is required or not
* added fields address area and state (area is hidden by default)

**2.0.21** - 2013-10-30

* added field `contact_login` to the overview table

**2.0.20** - 2013-10-25

* if not given automatically set value for `contact_since` on inserting a new contact
* added `contact_status` `PENDING` to tables `contact_contact` and `contact_overview`

**2.0.19** - 2013-10-24

* added parameter `$tags` to restrict the search to given TAGs
* add `selectOverview()` to get a fast overview of contact
* add `select()` to class *Overview*
* added missing sanitize commands and comments

**2.0.18** - 2013-10-21

* added full search function for the contacts

**2.0.17** - 2013-10-18

* added the fields `address_area` and `address_state` to the table `contact_overview`

**2.0.16** - 2013-10-08

* fixed a strong typo which caused the returning of a wrong contact ID 
* added `issetContactTag()` to contact control 

**2.0.15** - 2013-09-25

* fixed wrong template path
* changed secured access to users

**2.0.14** - 2013-09-23

* prepared beta test

**2.0.13** - 2013-09-06

* added import for installed KeepInTouch 0.72+
* added handling for extra fields

**2.0.12** - 2013-08-07

* Contact is now using controllers in classes

**2.0.11** - 2013-07-25

* first beta release

**2.0.10** - 2013-06-21

* initial release