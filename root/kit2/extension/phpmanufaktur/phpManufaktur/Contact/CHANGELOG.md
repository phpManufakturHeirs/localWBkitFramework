## Contact for kitFramework ##

(c) 2013 phpManufaktur by Ralf Hertsch

MIT License (MIT) - <http://www.opensource.org/licenses/MIT>

kitFramework - <https://kit2.phpmanufaktur.de>

**2.0.27**

* add `icon.png` and `tool_icon.png` for BlackCat CMS and WebsiteBaker 2.8.4 support
* added `getPrimaryEMailAddress()` to Contact Control
* share Contact Control as $app['contact']

**2.0.26**

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