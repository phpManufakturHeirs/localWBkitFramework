## Event ##

(c) 2011, 2013 phpManufaktur by Ralf Hertsch

MIT License (MIT) - <http://www.opensource.org/licenses/MIT>

kitFramework - <https://kit2.phpmanufaktur.de>

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