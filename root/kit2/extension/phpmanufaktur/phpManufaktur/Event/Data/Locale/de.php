<?php

/**
 * kitFramework::Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 * This file was created by the kitFramework i18nEditor
 */

if ('á' != "\xc3\xa1") {
    // the language files must be saved as UTF-8 (without BOM)
    throw new \Exception('The language file ' . __FILE__ . ' is damaged, it must be saved UTF-8 encoded!');
}

return array(
  '%type% Import'
    => '%type% Import',
  '- delete field -'
    => '- Feld löschen -',
  '- new extra field -'
    => '- neues Zusatzfeld -',
  '- new group -'
    => '- neue Gruppe -',
  'About'
    => '?',
  'Account type'
    => 'Benutzerkonto, Typ',
  'Activate the desired account role'
    => 'Das beantragte Benutzerrecht aktivieren',
  'Add a image'
    => 'Ein Bild hinzufügen',
  'Add a new group'
    => 'Eine neue Gruppe hinzufügen',
  'Add a subscription'
    => 'Eine Anmeldung hinzufügen',
  'Admin Status'
    => 'Administrator Status',
  'At day x of month must be greater than zero and less than 28.'
    => 'Der Wert für Ausführen am x. Tag des Monats muss größer als Null und kleiner als 28 sein.',
  'At least we need one communication channel, so please tell us a email address, phone or a URL'
    => 'Wir benötigen mindestens einen Kommunikationsweg, bitte nennen Sie uns eine E-Mail Adresse, Telefonummer oder die URL der Homepage.',
  'At the first'
    => 'Am ersten',
  'At the first and third'
    => 'Am ersten und dritten',
  'At the fourth'
    => 'Am vierten',
  'At the last'
    => 'Am letzten',
  'At the moment there are no proposed events'
    => 'Momentan liegen keine Veranstaltungsvorschläge vor.',
  'At the moment there are no subscriptions for your events'
    => 'Momentan liegen keine Anmeldungen zu Ihren Veranstaltungen vor.',
  'At the second'
    => 'Am zweiten',
  'At the second and fourth'
    => 'Am zweiten und vierten',
  'At the second and last'
    => 'Am zweiten und letzten',
  'At the third'
    => 'Am dritten',
  'but not at %dates%.'
    => ', jedoch nicht am %dates%.',
  'by copying from a existing event'
    => 'durch Kopieren einer existierenden Veranstaltung',
  'by selecting a event group'
    => 'durch Auswahl einer Veranstaltungsgruppe',
  'Canceled'
    => 'Wiederrufen',
  'Change account rights'
    => 'Änderung der Benutzerrechte',
  'Change Event configuration'
    => 'Event Konfiguration ändern',
  'Checking the GUID identifier'
    => 'Überprüfung der GUID Kennung',
  'CHOICE_ADMIN_ACCOUNT'
    => 'Ich möchte als Administrator alle Veranstaltungen bearbeiten können',
  'CHOICE_LOCATION_ACCOUNT'
    => 'Ich vertrete einen Veranstaltungsort und möchte Veranstaltungen bearbeiten können, die dort stattfinden',
  'CHOICE_ORGANIZER_ACCOUNT'
    => 'Ich vertrete einen Veranstalter, Verein oder eine Organisation und möchte deren Veranstaltungen bearbeiten können',
  'CHOICE_SUBMITTER_ACCOUNT'
    => 'Ich möchte Veranstaltungen bearbeiten können, die ich übermittelt habe',
  'Click to subscribe'
    => 'Anklicken um sich zu dieser Veranstaltung anzumelden',
  'closed'
    => 'geschlossen',
  'Comments handling'
    => 'Kommentar Behandlung',
  'company, institution or association'
    => 'Firma, Institution oder Verein',
  'Confirmed'
    => 'Bestätigt',
  'Costs'
    => 'Teilnahmegebühr',
  'Create a new category'
    => 'Eine neue Kategorie anlegen',
  'Create a new event'
    => 'Eine neue Veranstaltung erstellen',
  'Create a new extra field'
    => 'Ein neues Zusatzfeld anlegen',
  'Create a new Location record'
    => 'Einen neuen Veranstaltungsort anlegen',
  'Create a new Organizer record'
    => 'Eine neue Veranstalter Adresse anlegen',
  'Create a new recurring event with the ID %event_id%'
    => 'Neue sich wiederholende Veranstaltung mit der ID %event_id% angelegt.',
  'Create a new title'
    => 'Eine neue Schlagzeile anlegen',
  'Create event'
    => 'Veranstaltung anlegen',
  'Create or edit a event'
    => 'Veranstaltung anlegen oder bearbeiten',
  'Created the tag %tag% in Contact.'
    => 'Das Schlagwort %tag% wurde in Contact angelegt.',
  'Daily recurring'
    => 'Tägliche Wiederholung',
  'Date'
    => 'Datum',
  'Date and Time'
    => 'Datum und Uhrzeit',
  'Day sequence'
    => 'Alle x-Tage wiederholen',
  'Day type'
    => 'Typ',
  'Deadline'
    => 'Anmeldeschluß',
  'Delete this image'
    => 'Dieses Bild löschen',
  'Description long'
    => 'Beschreibung',
  'Description short'
    => 'Zusammenfassung',
  'Description title'
    => 'Titel',
  'Detected a kitEvent installation (Release: %release%) with %count% active or locked events.'
    => 'Es wurde eine kitEvent Installation (Release: %release%) mit %count% Veranstaltungen gefunden, die importiert werden können.',
  'Do not know how to handle the recurring type <b>%type%</b>.'
    => 'Weiß nicht, wie der Wiederholungstyp <strong>%type%</strong> zu handhaben ist.',
  'Do not publish the event'
    => 'Veranstaltung nicht veröffentlichen',
  'Don\'t know how to handle the month type %type%'
    => 'Unbekannter Monatstyp %type%',
  'Don\'t know how to handle the recurring type %type%.'
    => 'Unbekannter Wiederholungstyp %type%.',
  'Don\'t know how to handle the year type %type%'
    => 'Weiß nicht, wie ich den Jahrestyp %type% behandeln soll!',
  'Edit event'
    => 'Veranstaltung bearbeiten',
  'email usage'
    => 'Verwendung',
  'event'
    => 'Veranstaltung',
  'Event'
    => 'Veranstaltung',
  'Event Administration - About'
    => 'Event Verwaltung - Über',
  'Event Administration - Copy Event'
    => 'Event Administration - Veranstaltung kopieren',
  'Event Administration - Create or edit event'
    => 'Event Administration - Veranstaltung erstellen oder bearbeiten',
  'Event costs'
    => 'Eintrittspreis',
  'Event date from'
    => 'Beginn der Veranstaltung',
  'Event date to'
    => 'Ende der Veranstaltung',
  'Event deadline'
    => 'Anmeldeschluß',
  'Event group'
    => 'Gruppe',
  'Event id'
    => 'Veranstaltung ID',
  'Event ID'
    => 'ID',
  'Event list'
    => 'Veranstaltungen, Übersicht',
  'Event location'
    => 'Veranstaltungsort',
  'Event management suite for freelancers and organizers'
    => 'Veranstaltungen, Konzerte, Seminare oder Vorlesungen verwalten und organisieren',
  'Event participants confirmed'
    => 'Tln. best.',
  'Event participants max'
    => 'max. Tln.',
  'Event publish from'
    => 'Veröffentlichen ab',
  'Event publish to'
    => 'Veröffentlichen bis',
  'Event status'
    => 'Status',
  'Event successfull updated'
    => 'Die Veranstaltung wurde aktualisiert',
  'Event Title'
    => 'Titel der Veranstaltung',
  'Event url'
    => 'Veranstaltungs URL',
  'Exclude dates'
    => 'Daten ausschließen',
  'Extra field'
    => 'Zusatzfeld',
  'Float'
    => 'Dezimalzahl',
  'free of charge'
    => 'kostenlos',
  'Google Map'
    => 'Google Map',
  'Group'
    => 'Gruppe',
  'Group description'
    => 'Beschreibung',
  'Group extra fields'
    => 'Zusatzfelder',
  'Group id'
    => 'ID',
  'Group location contact tags'
    => 'Veranstaltungsorte',
  'Group name'
    => 'Gruppen Bezeichner',
  'Group name (translated)'
    => 'Gruppen Bezeichner (übersetzt)',
  'Group organizer contact tags'
    => 'Veranstalter',
  'Group participant contact tags'
    => 'Teilnehmer',
  'Group status'
    => 'Status',
  'Groups'
    => 'Gruppen',
  'Hello %name%'
    => 'Hallo %name%',
  'I accept the <a href="%url%" target="_blank">general terms and conditions</a>'
    => 'Ich akzeptiere die <a href="%url%" target="_blank">AGB</a>',
  'I really don\'t know the Organizer'
    => 'Der Veranstalter ist mir leider nicht bekannt',
  'If you are prompted to login, please use your username and password'
    => 'Wenn Sie aufgefordert werden sich anzumelden, verwenden Sie bitte Ihren Benutzernamen und Ihr Passwort',
  'Ignore existing comments'
    => 'Existierende Kommentare werden nicht übernommen',
  'Import events from kitEvent'
    => 'Veranstaltungen aus kitEvent importieren',
  'Information about the Event extension'
    => 'Informationen über die Event Extension',
  'Integer'
    => 'Ganzzahl',
  'Invalid key => value pair in the set[] parameter!'
    => 'Ungültiges Schlüssel => Wert Paar für den set[] Parameter!',
  'Invalid login'
    => 'Ungültiger Login, Benutzername oder Passwort falsch',
  'Invalid submission, please try again'
    => 'Ungültige Übermittlung, bitte versuchen Sie es erneut!',
  'It is not allowed that the event start in the past!'
    => 'Der Veranstaltungsbeginn darf nicht in der Vergangenheit liegen!',
  'List of actual submitted proposes for events'
    => 'Übersicht über die aktuellen Vorschläge zu Veranstaltungen',
  'List of all active events'
    => 'Übersicht über alle aktiven Veranstaltungen',
  'List of all available contacts (Organizer, Locations, Participants)'
    => 'Übersicht über alle verfügbaren Kontakte (Veranstalter, Orte, Teilnehmer)',
  'List of all available event groups'
    => 'Übersicht über alle verfügbaren Veranstaltungsgruppen',
  'List of all subscriptions for events'
    => 'Übersicht über alle Anmeldungen zu Veranstaltungen',
  'List single dates in format <b>%format%</b> separated by comma to exclude them from recurring'
    => 'Schließen Sie einzelne Daten im Format <b>%format%</b> durch ein Komma getrennt von der Sequenz aus.',
  'Location'
    => 'Veranstaltungsort',
  'Location ID'
    => 'Veranstaltungsort ID',
  'Long description'
    => 'Langbeschreibung',
  'Message from the kitFramework Event application'
    => 'Mitteilung von kitFramework Event',
  'Migrate data of a kitEvent installation into Event'
    => 'Veranstaltungsdaten von einer kitEvent Installation in Event übernehmen',
  'Missing a valid Event ID!'
    => 'Vermisse eine gültige Veranstaltungs ID!',
  'Month pattern day'
    => 'Tag',
  'Month pattern day view'
    => 'Wochentag',
  'Month pattern sequence'
    => 'Wiederholung',
  'Month pattern type'
    => 'Typ',
  'Month sequence day'
    => 'Tag',
  'Month sequence day view'
    => 'Wochentag',
  'Month sequence month'
    => 'Monat',
  'Month type'
    => 'Typ',
  'month_pattern_type'
    => 'Typ',
  'Monthly recurring'
    => 'Monatliche Wiederholung',
  'natural person'
    => 'Natürliche Person',
  'New event id'
    => 'ID',
  'New location'
    => 'Neuer Veranstaltungsort',
  'New organizer'
    => 'Neuer Veranstalter',
  'New password'
    => 'Neues Passwort',
  'Next event dates'
    => 'Die nächsten Veranstaltungstermine',
  'No image selected, nothing to do.'
    => 'Es wurde keine Abbildung ausgewählt.',
  'No recurring event'
    => 'Keinen Serientermin festlegen',
  'No recurring event type selected'
    => 'Es wurde kein Serientermin Typ ausgewählt.',
  'No results for this filter!'
    => 'Dieser Filter lieferte kein Ergebnis!',
  'Organizer'
    => 'Veranstalter',
  'Organizer ID'
    => 'Veranstalter ID',
  'Parent event id'
    => 'ID',
  'Participant'
    => 'Teilnehmer',
  'Participants canceled'
    => 'Teilnehmer, storniert',
  'Participants confirmed'
    => 'Teilnehmer, bestätigt',
  'Participants maximum'
    => 'Teilnehmer, max. Anzahl',
  'Participants pending'
    => 'Teilnehmer, unbestätigt',
  'Participants total'
    => 'Teilnehmer, angemeldet',
  'Participants, maximum'
    => 'Teilnehmer, max.',
  'Participants, total'
    => 'Teilnehmer, insgesamt',
  'Pass comments from parent'
    => 'Kommentare werden aktiv von der ursprünglichen Veranstaltung vererbt',
  'Permalink successfull changed'
    => 'Der Permanent Link wurde erfolgreich geändert',
  'personal email address'
    => 'persönliche E-Mail Adresse',
  'Pictures'
    => 'Bilder',
  'Please check the event data and use one of the following action links'
    => 'Bitte prüfen Sie die Angaben zu der Veranstaltung und verwenden Sie anschließend einen der folgenden Aktions-Links',
  'Please define a permanent link in config.event.json. Without this link Event can not create permanent links or respond to user requests.'
    => 'Bitte definieren Sie einen permanenten Link in der config.event.json. Ohne diesen Link kann Event keine Verweise auf Veranstaltungen erzeugen oder auf Anfragen von Veranstaltungsteilnehmern reagieren.',
  'Please determine the handling for the comments.'
    => 'Bitte legen Sie die Handhabung für die Kommentare fest.',
  'Please feel free to order a account.'
    => 'Fordern Sie ein Benutzerkonto an',
  'Please search for for a location or select the checkbox to create a new one.'
    => 'Bitte suchen Sie nach einem Veranstaltungsort oder haken Sie die Checkbox an um einen neuen Veranstaltungsort anzulegen.',
  'Please search for for a organizer or select the checkbox to create a new one.'
    => 'Bitte suchen Sie nach einem Veranstalter oder haken Sie die Checkbox an um einen neuen Veranstalter anzulegen.',
  'Please search for the contact you want to subscribe to an event or add a new contact, if you are shure that the person does not exists in Contacts.'
    => 'Bitte suchen Sie nach dem Kontakt, den Sie zu einer Veranstaltung anmelden möchten. Fügen Sie einen neuen Kontakt hinzu, falls dieser noch nicht existiert.',
  'Please search for the event you want to copy data from.'
    => 'Bitte suchen Sie nach der Veranstaltung, die Sie kopieren möchten.',
  'Please select action'
    => 'Bitte wählen Sie eine Aktion',
  'Please select at least one weekday!'
    => 'Bitte wählen Sie mindestens einen Wochentag aus!',
  'Please select at minimum one tag for the %type%.'
    => 'Bitte legen Sie mindestens eine Markierung für %type% fest!',
  'Please select the event you want to copy into a new one'
    => 'Wählen Sie die Veranstaltung aus, deren Daten für eine neue Veranstaltung übernommen werden sollen.',
  'Please type in a long description with %minimum% characters at minimum.'
    => 'Bitte geben Sie eine Langbeschreibung mit einer Länge von mindestens %minimum% Zeichen ein.',
  'Please type in a short description with %minimum% characters at minimum.'
    => 'Bitte geben Sie eine Kurzbeschreibung mit einer Länge von mindestens %minimum% Zeichen ein.',
  'Please type in a title with %minimum% characters at minimum.'
    => 'Bitte geben Sie einen Titel mit einer Länge von mindestens %minimum% Zeichen ein.',
  'Please use the parameter set[] to set a configuration value.'
    => 'Bitte verwenden Sie den Paramter set[] um einen Konfigurationswert zu setzen!',
  'Proposed event: %event%'
    => 'Vorgeschlagene Veranstaltung: %event%',
  'Proposes'
    => 'Vorschläge',
  'Publish'
    => 'Veröffentlichen',
  'Publish from'
    => 'Veröffentlichen ab',
  'Publish the event'
    => 'Veranstaltung veröffentlichen',
  'Publish to'
    => 'Veröffentlichen bis',
  'Received request'
    => 'Anfrage erhalten',
  'Recurring date end'
    => 'Letzte Wiederholung',
  'Recurring event'
    => 'Serientermin',
  'Recurring id'
    => 'ID',
  'Recurring type'
    => 'Typ',
  'Redirect to the parent event ID!'
    => 'Umgeleitet auf die ursprüngliche Veranstaltungs ID!',
  'regular email address of a company, institution or association'
    => 'offizielle E-Mail Adresse einer Firma, Einrichtung oder eines Verein',
  'Reject the desired account role'
    => 'Das gewünschte Benutzerrecht zurückweisen',
  'Reject this event'
    => 'Veranstaltung ablehnen',
  'Remark'
    => 'Bemerkung',
  'Repeat at workdays'
    => 'An Werktagen wiederholen',
  'Repeat each x-days'
    => 'Alle x-Tage wiederholen',
  'Repeat x-month must be greater than zero and less then 13.'
    => 'Der Wert für Wiederhole jeden x. Monat muß größer als Null und kleiner als 13 sein.',
  'Rewrite the the recurring event'
    => 'Wiederholende Veranstaltung umschreiben',
  'ROLE_EVENT_ADMIN'
    => 'Benutzerrecht: Veranstaltungen als Administrator bearbeiten',
  'ROLE_EVENT_LOCATION'
    => 'Benutzerrecht: Veranstaltungen bearbeiten, die diesem Veranstaltungsort zugewiesen sind',
  'ROLE_EVENT_ORGANIZER'
    => 'Benutzerrecht: Veranstaltungen bearbeiten, die von diesem Veranstalter zugewiesen sind',
  'ROLE_EVENT_SUBMITTER'
    => 'Benutzerrecht: Veranstaltungen bearbeiten, die von diesem Benutzer vorgeschlagen wurden',
  'Search event'
    => 'Veranstaltung suchen',
  'Search Location'
    => 'Veranstaltungsort suchen',
  'Search Organizer'
    => 'Veranstalter suchen',
  'second'
    => 'zweiten',
  'second_fourth'
    => 'zweiten und vierten',
  'second_last'
    => 'zweiten und letzten',
  'Select account type'
    => 'Kontotyp wählen',
  'Select event'
    => 'Veranstaltung auswählen',
  'Select event group'
    => 'Veranstaltungsgruppe auswählen',
  'Select group'
    => 'Gruppe wählen',
  'Select type'
    => 'Typ auswählen',
  'Short description'
    => 'Kurzbeschreibung',
  'Show detailed information'
    => 'Detailierte Informationen anzeigen',
  'Skipped kitEvent ID %event_id%: Can not determine the Event Group ID for the kitEvent Group ID %group_id%.'
    => 'kitEvent ID %event_id% übersprungen: Kann die Veranstaltungs Gruppen ID  für die ID %group_id% nicht ermitteln!',
  'Skipped kitEvent ID %event_id%: Can not find the contact ID for the KIT ID %kit_id%.'
    => 'kitEvent ID %event_id% übersprungen: Konnte die zugehörige Contact ID für die KIT ID %kit_id% nicht finden.',
  'Skipped kitEvent ID %event_id%: Can not read the items for this event.'
    => 'kitEvent ID %event_id% übersprungen: Kann die Einträge zu dieser Veranstaltung nicht lesen.',
  'Skipped kitEvent ID %event_id%: No valid value in %field%'
    => 'kitEvent ID <b>%event_id%</b> übersprungen: Ungültiger Wert in Feld %field%',
  'Skipped kitEvent ID %event_id%: This entry exists already as Event ID %id%.'
    => 'kitEvent ID %event_id% übersprungen: Dieser Eintrag existiert bereit als Event ID %id%',
  'Start a new search'
    => 'Eine neue Suche starten',
  'Start import from kitEvent'
    => 'Import aus kitEvent starten',
  'Start search'
    => 'Suche starten',
  'Submit subscription'
    => 'Anmeldung übermitteln',
  'Submitter ID'
    => 'Übermittler ID',
  'Submitter Status'
    => 'Übermittler Status',
  'Subscribe'
    => 'Anmelden',
  'Subscribe to event'
    => 'Zu der Veranstaltung anmelden',
  'Subscriber'
    => 'Anmeldender',
  'Subscription id'
    => 'ID',
  'Subscriptions'
    => 'Anmeldungen',
  'Successfull inserted a recurring event'
    => 'Es wurden erfolgreich sich wiederholende Veranstaltungen angelegt.',
  'Text - 256 characters'
    => 'Text - max. 256 Zeichen',
  'Text - HTML'
    => 'Text - HTML formatiert',
  'Text - plain'
    => 'Text - unformatiert',
  'Thank you for proposing the following event'
    => 'Vielen Dank für Ihren Veranstaltungsvorschlag',
  'Thank you for your subscription, we have send you a receipt at your email address.'
    => 'Vielen Dank für Ihre Anmeldung, wir haben Ihnen eine Bestätigung an Ihre E-Mail Adresse gesendet.',
  'Thank you for your subscription. We have send you an email, please use the submitted confirmation link to confirm your email address and to activate your subscription!'
    => 'Vielen Dank für Ihre Anmeldung. Wir haben Ihnen eine E-Mail geschickt, bitte benutzen Sie den enthaltenen Bestätigungslink um Ihre E-Mail Adresse zu bestätigen und die Anmeldung zu aktivieren.',
  'Thank you, %name%'
    => 'Vielen Dank, %name%',
  'Thank you, one of the admins will approve your request and contact you.'
    => 'Vielen Dank, ein Administrator wird Ihre Anfrage prüfen und sich mit Ihnen in Verbindung setzen.',
  'The \'limit\' filter must be a integer value.'
    => 'Der Parameter <em>limit</em> muss eine Ganzzahl sein.',
  'The action link was successfull executed'
    => 'Der Aktionslink wurde erfolgreich ausgeführt',
  'The change of your account rights is approved by admin'
    => 'Die Änderung Ihrer Benutzerrechte wurde durch den Administrator genehmigt',
  'The change of your account rights is rejected by admin'
    => 'Die Änderung Ihrer Benutzerrechte wurde durch den Administrator abgelehnt',
  'The contact record was successfull updated.'
    => 'Der Adressdatensatz wurde aktualisiert.',
  'The daily sequence must be greater than zero!'
    => 'Die Sequenz der täglichen Wiederholung muss größer als Null sein!',
  'The deadline ends after the event start date!'
    => 'Der Anmeldeschluß liegt nach dem Beginn der Veranstaltung!',
  'The email address %email% is associated with a company contact record. At the moment you can only subscribe to a event with your personal email address!'
    => 'Die E-Mail Adresse %email% ist einer Firma oder Institution zugeordnet. Zur Zeit können Sie sich jedoch nur mit einer persönlichen E-Mail Adresse zu einer Veranstaltung anmelden.',
  'The email address %email% is not registered. We can only create a account for you if there was already a interaction, i.e. you have proposed a event. If you represent an organizer or a location and your public email address is not registered, please contact the administrator.'
    => 'Die E-Mail Adresse %email% ist nicht registriert. Wir können nur dann ein Benutzerkonto für Sie anlegen, wenn bereits eine Interaktion stattgefunden hat, z.B. in dem Sie bereits eine Veranstaltung vorgeschlagen haben. Falls Sie einen Veranstalter oder einen Veranstaltungsort vertreten und ihre <em>öffentliche</em> E-Mail Adresse noch nicht registriert ist, nehmen Sie bitte Kontakt mit dem Administrator auf.',
  'The email field must be always set for the subscription form and always enabled and required! Please check the config.event.json!'
    => 'Das E-Mail Feld muss für Anmeldeformulare immer gesetzt werden und es muss sichtbar und als <em>benötigt</em> gekennzeichnet sein! Bitte prüfen Sie die <em>config.event.json</em>!',
  'The event %title% was just published by the administrator'
    => 'Die Veranstaltung %title% wurde gerade durch den Administrator veröffentlicht.',
  'The event [%event_id%] will be repeated at %pattern_type% %pattern_day% of each %pattern_sequence%. month%exclude%'
    => 'Die Veranstaltung [%event_id%] wird am %pattern_type% %pattern_day% jedes %pattern_sequence%. Monat wiederholt%exclude%',
  'The event [%event_id%] will be repeated at each workday%exclude%'
    => 'Die Veranstaltung [%event_id%] wird an jedem Werktag wiederholt%exclude%',
  'The event [%event_id%] will be repeated at the %month_day%. day of each %month_sequence%. month%exclude%'
    => 'Die Veranstaltung [%event_id%] wird am %month_day%. Tag jedes %month_sequence%. Monat wiederholt%exclude%',
  'The event [%event_id%] will be repeated each %day_sequence% day(s)%exclude%'
    => 'Die Veranstaltung [%event_id%] wird jeden %day_sequence%. Tag wiederholt%exclude%',
  'The event [%event_id%] will be repeated each %week_sequence% week(s) at %week_day%%exclude%'
    => 'Die Veranstaltung [%event_id%] wird jede %week_sequence%. Woche am %week_day% wiederholt%exclude%',
  'The event [%event_id%] will be repeated each %year_repeat%. year at %month_day%. %month_name%%exclude%'
    => 'Die Veranstaltung [%event_id%] wird jedes %year_repeat%. Jahr am %month_day%. %month_name% wiederholt%exclude%',
  'The event [%event_id%] will be repeated each %year_repeat%. year at %pattern_type% %pattern_day% of %pattern_month%%exclude%'
    => 'Die Veranstaltung [%event_id%] wird jedes %year_repeat%. Jahr am %pattern_type% %pattern_day% im %pattern_month% wiederholt%exclude%',
  'The event group with the name %group% does not exists!'
    => 'Die Veranstaltungs-Gruppe %group% existiert nicht!',
  'The event list is empty, please create a event!'
    => 'Es existieren keine aktiven Veranstaltungen, legen Sie eine neue Versanstaltung an.',
  'The event start date is behind the event end date!'
    => 'Das Anfangsdatum der Veranstaltung liegt nach dem Enddatum der Veranstaltung!',
  'The event with the title %title% was published.'
    => 'Die Veranstaltung mit der Bezeichnung %title% wurde veröffentlicht.',
  'The event with the title %title% was rejected.'
    => 'Die Veranstaltung mit der Bezeichnung %title% wurde zurückgewiesen.',
  'The field with the name %name% is not supported.'
    => 'Das Feld mit dem Bezeichner %name% wird nicht unterstützt.',
  'The filter \'actual\' must be numeric or contain the keyword \'current\' as first parameter.'
    => 'Der Filter <em>actual</em> muss eine Ganzzahl oder das Schlüsselwort <em>current</em> als ersten Parameter enthalten.',
  'The filter for <em>day</em> must be numeric or contain the keyword <em>current</em>'
    => 'Der Filter für <em>day</em> muss eine Ganzzahl oder das Schlüsselwort <em>current</em> enthalten.',
  'The filter for <em>month</em> must be numeric or contain the keyword <em>current</em>'
    => 'Der Filter <em>month</em> muss eine Ganzzahl oder das Schlüsselwort <em>current</em> enthalten.',
  'The filter for <em>year</em> must be numeric or contain the keyword <em>current</em>'
    => 'Der Filter für <em>year</em> muss eine Ganzzahl oder das Schlüsselwort <em>current</em> enthalten.',
  'The group list is empty, please define a group!'
    => 'Es existieren keine Gruppen, bitte legen Sie eine Gruppe an!',
  'The identifier %identifier% already exists!'
    => 'Der Bezeichner %identifier% existiert bereits!',
  'The image <b>%image%</b> has been added to the event.'
    => 'Der Veranstaltung wurde das Bild <b>%image%</b> hinzugefügt.',
  'The image with the ID %image_id% was successfull deleted.'
    => 'Das Bild mit der ID %image_id% wurde erfolgreich gelöscht.',
  'The import from kitEvent was successfull finished.'
    => 'Der Import aus kitEvent wurde abgeschlossen.',
  'The next recurring events'
    => 'Die nächsten Veranstaltungstermine',
  'The publishing date ends before the event starts, this is not allowed!'
    => 'Der Veröffentlichungszeitraum endet vor dem Beginn der Veranstaltung, dies ist nicht gewünscht!',
  'The publishing of the event %title% was rejected by the administrator'
    => 'Die Veröffentlichung der Veranstaltung %title% wurde vom Administrator zurückgewiesen.',
  'The QR-Code file does not exists, please rebuild all QR-Code files.'
    => 'Die QR-Code Datei existiert nicht, bitte erzeugen Sie alle QR-Code Dateien neu.',
  'The recurring event was not changed.'
    => 'Die wiederholende Veranstaltung wurde nicht geändert.',
  'The recurring events where successfull deleted.'
    => 'Die wiederholten Veranstaltungen wurden erfolgreich gelöscht.',
  'The repeat each x-year sequence must be greater than zero and less than 10!'
    => 'Der Wert für die jährliche Wiederholung muss größer als Null und kleiner als 10 sein!',
  'The second parameter for the filter \'actual\' must be a positive integer value.'
    => 'Der zweite Parameter für den Filter <em>actual</em> muss eine positive Ganzzahl sein.',
  'The second parameter for the filter \'actual\' must be positive integer value.'
    => 'Der zweite Parameter für den Filter <em>actual</em> muss eine positive Ganzzahl sein.',
  'The status (%subscription_status%) of your subscription #%subscription_id% is ambiguous, the program can not confirm your subscription. Please contact the <a href="%email%">webmaster</a>.'
    => 'Der Status (%subscription_status%) Ihrer Anmeldung #%subscription_id% ist widersprüchlich, das Programm kann Ihre Anmeldung leider nicht bestätigen. Bitte kontaktieren Sie den <a href="%email%">Webmaster</a>.',
  'The status for the contact with the ID %contact_id% is ambiguous, the program can not activate the account. Please contact the <a href="%email%">webmaster</a>.'
    => 'Der Status für den Kontaktdatensatz mit der ID %contact_id% ist nicht eindeutig, das Programm kann Ihr Benutzerkonto nicht aktivieren. Bitte kontaktieren Sie den <a href="%email%">webmaster</a>.',
  'The status of your address record is actually %status%, so we can not accept your subscription. Please contact the <a href="mailto:%email%">webmaster</a>.'
    => 'Der Status Ihres Adressdatensatz ist zur Zeit auf %status% gesetzt, wir können Ihre Anmeldung daher nicht entgegennehmen. Bitte nehmen Sie Kontakt mit dem <a href="mailto:%email%">Webmaster</a> auf, um die Situation zu klären.',
  'The submitted GUID %guid% does not exists.'
    => 'Die übermittelte GUID %guid% existiert nicht!',
  'The subscription was successfull inserted.'
    => 'Die Anmeldung wurde erfolgreich hinzugefügt.',
  'The subscription was successfull updated'
    => 'Die Anmeldung wurde erfolgreich aktualisiert.',
  'The Subscription with the ID %subscription_id% does not exists!'
    => 'Die Anmeldung mit der ID %subscription_id% existiert nicht!',
  'The user %contact_name% with the ID %contact_id% and the email address %email% has proposed the following event'
    => 'Der Kontakt %contact_name% mit der der ID %contact_id% und der E-Mail Adresse %email% hat die folgende Veranstaltung vorgeschlagen',
  'The user %user% does not exists!'
    => 'Der Benutzer %user% existiert nicht!',
  'The user %user% has already proposed %count% events'
    => 'Der Kontakt %user% hat bereits %count% Veranstaltungen vorgeschlagen',
  'The user %user% has never proposed a event'
    => 'Der Kontakt %user% hat noch nie eine Veranstaltung vorgeschlagen',
  'The user %user% want to get the right'
    => 'Der Benutzer %user% möchte die Berechtigung erhalten',
  'The value for \'order_direction\' can be \'ASC\' (ascending) or \'DESC\' (descending)'
    => 'Der Wert für <em>order_direction</em> kann <em>ASC</em> (aufsteigend) oder <em>DESC</em> (absteigend) sein.',
  'The weekly sequence must be greater than zero!'
    => 'Die Sequenz der wöchentlichen Wiederholung muss größer als Null sein!',
  'There exists no kitEvent installation at the parent CMS!'
    => 'Es wurde keine kitEvent Installation in dem übergeordeneten Content Management System gefunden!',
  'There exists no locations who fits to the search term %search%'
    => 'Es wurde kein Veranstaltungsort gefunden, der zu dem Suchbegriff <i>%search%</i> passt.',
  'There exists no organizer who fits to the search term %search%'
    => 'Es wurde kein Veranstalter gefunden, der zu dem Suchbegriff <i>%search%</i> passt.',
  'third'
    => 'dritten',
  'This activation link was already used and is no longer valid!'
    => 'Dieser Aktivierungslink wurde bereits verwendet und ist nicht mehr gültig!',
  'This event was copied from the event with the ID %id%. Be aware that you should change the dates before publishing to avoid duplicate events!'
    => 'Diese Veranstaltung ist eine Kopie der Veranstaltung mit der ID %id%. Bitte beachten Sie, daß Sie die Datumsangaben anpassen bevor Sie diese Veranstaltung veröffentlichen - Sie erzeugen sonst doppelte Einträge!',
  'This extra field is used in the event group %group%. First remove the extra field from the event group.'
    => 'Dieses Zusatzfeld wird in der Veranstaltungs Gruppe %group% verwendet. Sie müssen das Zusatzfeld zunächst aus der Gruppe entfernen.',
  'This user has a account but was never in contact in context with events'
    => 'Dieser Benutzer verfügt über ein Benutzerkonto, war jedoch im Zusammenhang mit Veranstaltungen noch nie im Kontakt',
  'This user has a contact record but was never in contact in context with events'
    => 'Dieser Benutzer verfügt über einen Kontaktdatensatz, war jedoch im Zusammenhang mit Veranstaltungen noch nie im Kontakt',
  'Type'
    => 'Typ',
  'Unknown organizer'
    => 'Unbekannter Veranstalter',
  'unlimited'
    => 'unbegrenzt',
  'Using qrcode[] is not enabled in config.event.json!'
    => 'qrcode[] ist nicht in der config.event.json freigegeben!',
  'Visit the event description'
    => 'Besuchen Sie die Veranstaltungsbeschreibung',
  'We have send you a new password, please check your email account'
    => 'Wir haben Ihnen ein neues Passwort an Ihre E-Mail Adresse gesendet, bitte prüfen Sie Ihren Posteingang',
  'We send you a new password to your email address.'
    => 'Wir senden Ihnen ein neues Passwort an Ihre E-Mail Adresse.',
  'Week day'
    => 'Wochentag',
  'Week day view'
    => 'Wochentage',
  'Week sequence'
    => 'Wöchentliche Wiederholung',
  'Weekly recurring'
    => 'Wöchentliche Wiederholung',
  'Year pattern day'
    => 'Tag',
  'Year pattern day view'
    => 'Tag',
  'Year pattern month'
    => 'Monat',
  'Year pattern month view'
    => 'Monate',
  'Year pattern type'
    => 'Typ',
  'Year repeat'
    => 'Jährliche Wiederholung',
  'Year sequence day'
    => 'Tag',
  'Year sequence day view'
    => 'Tage',
  'Year sequence month'
    => 'Monat',
  'Year sequence month view'
    => 'Monate',
  'Year type'
    => 'Typ',
  'Yearly recurring'
    => 'Jährliche Wiederholung',
  'You have already subscribed to this Event at %datetime%, you can not subscribe again.'
    => 'Sie haben sich am %datetime% bereits zu dieser Veranstaltung angemeldet und können sich deshalb nicht erneut anmelden.',
  'You have already the right to edit events (%role%). Please contact the administrator if you want to change or extend your account rights'
    => 'Sie verfügen bereits über das Recht Veranstaltungen zu bearbeiten (%role%). Bitte wenden Sie sich an den Administrator, wenn Sie Ihre Rechte ändern oder erweitern möchten.',
  'You have now the additional right to: "%role%'
    => 'Sie verfügen jetzt über das zusätzliche Recht: "%role%',
  'You have selected <i>Company, Institution or Association</i> as contact type, so please give us the name'
    => 'Sie haben <i>Firma, Institution oder Verein</i> als Kontakt Typ angegeben, bitte nennen Sie uns den Namen der Einrichtung.',
  'You have selected <i>natural person</i> as contact type, so please give us the last name of the person.'
    => 'Sie haben <i>natürliche Person</i> als Kontakt Typ gewählt, bitte nennen Sie uns den Nachnamen der Person.',
  'You need a account if you want to edit events. In general we will give accounts to all event organizers, locations and persons which submit events frequently.'
    => 'Sie benötigen ein Benutzerkonto um Veranstaltungen ändern und ergänzen zu können. Im Allgemeinen erhalten alle Veranstalter, Veranstaltungsorte sowie Personen, die regelmäßig Veranstaltungen eintragen, einen Zugang von uns.',
  'Your contact record is locked, so we can not perform any action. Please contact the administrator'
    => 'Ihr Kontakt Datensatz ist gesperrt, wir können keine Aktion durchführen. Bitte wenden Sie sich an den Administrator.',
  'Your subscription for the event %event% is already confirmed.'
    => 'Ihre Anmeldung für die Veranstaltung %event% wurde bereits bestätigt.',
);
