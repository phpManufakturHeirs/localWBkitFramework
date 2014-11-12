## Event ##

(c) 2011, 2013 phpManufaktur by Ralf Hertsch

MIT License (MIT) - <http://www.opensource.org/licenses/MIT>

kitFramework - <https://kit2.phpmanufaktur.de>

**2.0.45** - 2014-09-14

* fixed wrong release date
* changed toolbar handling
* changed source for translation
* fixed strong typo in `ROLE_MEDIABROWSER_USER`
* replaced usage of `ucfirst()` with `$app['utils']->humanize()`
* updated `@link` references
* updated info URL
* added URL for the changelog in the CMS Tool
* fixed template path at import function
* added entry point to import event data from kitEvent

**2.0.44** - 2014-09-05

* changed form 'number' fields to 'text' due compatibility reason

**2.0.43** - 2014-08-31 

* smaller changes to fix and improve the translation handling

**2.0.42** - 2014-08-11

* improved translation handling, added support for i18nEditor
* generate extended debug information if the form submission is not valid 
* removed no longer needed block with entry points
* changed default address and communication usage type to PRIMARY
* changed usage of the contact edit dialog
* changed usage of library to `/latest`
* removed deprecated folder in /Template - need another concept

**2.0.41** - 2014-06-18

* added counter for recurring events

**2.0.40** - 2014-06-04

* fixed problems handling subscriptions
* changed route for the MediaBrowser
* fixed some problems in handling subscriptions 
* added edit dialog for subscriptions in backend
* subscription list can now be sorted and filtered
* fixed some smaller problems in parameter checking

**2.0.39** - 2014-05-07

* generally changed usage of class `container` to `container-fluid`
* fixed the paging in backend event list if `pack_recurring` is activated
* add parameter `limit[]` for recurring events in `event.recurring.twig` template
* all language files are now loaded by the BASIC extension
* The backend navigation tabs can now configured over `config.event.json`
* Recurring events will be now shown with future dates in any event edit dialog to enable a direct access.
* Subscriptions can now also manually inserted in the backend subscription list

**2.0.38** - 2014-04-03

* add feature `pack_recurring` to enable the [packing of recurring events](https://github.com/phpManufaktur/kfEvent/wiki/list.event.json#columns) in the administrative event list.

**2.0.37** - 2014-04-01

* additional recurring patterns for month: `FIRST_THIRD`, `SECOND_FOURTH` and `SECOND_LAST`
* optional contact field for subscriptions: `phone`, `cell`, `birthday`, `street`, `zip`, `city` and `country`
* optional field for subscriptions: terms and conditions with link 

**2.0.36** - 2014-03-24

* field `extra_type_description` set to required to avoid problem in validation
* The subscribe form and the submitted mails have now also access to data of event, recurring and recurring_events - access them in your templates if needed.

**2.0.35** - 2014-03-18

* fixed: if search engines access to invalid PID's Event can now fallback to a given URL
* The event description templates set now a canonical link to the content

**2.0.34** - 2014-03-17

* added parameter `view[recurring]` to the kitCommand `~~ event ~~` to enable a special view of recurring events. Show the parent event and the next upcoming recurring dates.

**2.0.33** - 2014-03-17

* added recurring events by day, week, month or year in a sequence or by specific pattern
* fixed some smaller problems in handling of events

**2.0.32** - 2014-03-10

* multiple smaller bugfixes
* added dependency: Libray
* switch back to version numbers instead use of 'latest' in templates
* set explicit arg separator at `http_build_query()` to avoid problems if the server uses another one
* changed dynamically usage of Carbon to static usage to avoid problems
* changed templates to usage with the central Library
* introduce /Template/deprecated and prepare changing of /command templates to Bootstrap 3
* add parameter `mode[]` to propose dialog to enable simplified forms
* cleanup CONTACT usage in propose dialog
* add a dialog to pass comments from parent event

**2.0.31** - 2014-01-22

* expand the width of the backend body container
* added security entry points
* added security role hierarchy
* fixed a wrong variable assignment in data `event`

**2.0.30** - 2014-01-17

* disabled function `cleanupEvents()` due possible problems with data integrity

**2.0.29** - 2014-01-07

* fixed: $messages are no longer in use and replaced by $alert
* improved check for Bootstrap 3

**2.0.28** - 2013-12-27

* changed the Event Backend to Bootstrap 3

**2.0.27** - 2013-12-20

* Improved import from previous kitEvent installations

**2.0.26** - 2013-12-20

* introduce Bootstrap 3 for the backend
* changed about dialog to Bootstrap 3
* added the function to copy events with all needed dialogs 

**2.0.25** - 2013-12-19

* added meta tag `generator`
* added `setPageTitle()` for events
* rewrite event propose. Now first step is to submit location, then organizer. The organizer can now also be `unknown`!
* suppress organizer indicated with `unknown.organizer@event.dummy.tld`

**2.0.24** - 2013-12-01

* fixed some typos and changed description

**2.0.23** - 2013-11-28

* check for a `scroll_to_id` parameter for the permanent link redirection
* added handling for `comments_info`

**2.0.22** - 2013-11-10

* missed `$roles` definition if user has a account but was never active
* changed the about dialog
* Changed to Font Awesome 4.0.3 (local available at *Contact*)

**2.0.21** - 2013-11-05

* fixed problem assigning Organizer, Location or Participant to the Event group
* fixed: wrong time converting for event_publish_to and event_publish_from
* fixed wrong linking for event title in backend event list
* added `action[config]` to enable configuration with the kitCommand `~~ event ~~`
* fixed wrong usage of the `event_id` in Event Detail view

**2.0.20** - 2013-11-04

* added support for additional vendor information
* introduce extended about dialog
* fixed: invalid class initialization in `Propose.php`
* hide table header if search return zero hits
* added sample to event.detail.twig how to hide emails which match a search pattern
* added backend icons for WebsiteBaker 2.8.4 and BlackCat

**2.0.19** - 2013-10-30

* add preferred choices for the country selection
* first public beta release

**2.0.18** - 2013-10-30

* event `description_title`, `description_short` and `description_long` can now set as required or not in `config.event.json`
* extended filter 'actual' with end terms: year, month and week
* avoid to show subscription information multiple in different iframes - uses null.twig to suppress
* added SERVER_EMAIL_NAME to confirmation mails
* add config[edit][frontend]
* enable different administrative emails (enhancement #10)
* added configuration for email addresses for confirming account and role actions
* introduce frontend editing with role checking, getting account, getting new password, etc.

**2.0.17** - 2013-10-25

* added status list for proposed events (backend)
* add rating to detailed search results
* bugfix: deleting extra fields does not remove the values and missed some checks to prevent trouble
* added subscription overview and handling to the backend

**2.0.16** - 2013-10-24

* added class *Propose* to enable a dialog guided proposing of new events for the visitors and supporters. Double-opt-in, activation for submitter and admin with different activation links

**2.0.15** - 2013-10-21

* implemented the *Contacts* full search function (backend)
* added a full search function for the events (backend and kitCommand)
* improved handling for extra fields, added examples in templates

**2.0.14** - 2013-10-18

* suppress the creation of an iCal file if a empty (new) event is created
* add `SESSION['EVENT_ID']` to enable checks by other extensions, i.e. *Comments*
* enable to skip the creation of an iCal file if a new (empty) event is created
* introduce event lists
* introduce filters for the event lists
* Introduce *Rating*  for event detail view and event lists

**2.0.13** - 2013-09-23

* just in progress

**2.0.11** - 2013-07-25

* first beta release

**2.0.10** - 2013-07-05

* initial release