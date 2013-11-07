<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if ('á' != "\xc3\xa1") {
    // the language files must be saved as UTF-8 (without BOM)
    throw new \Exception('The language file ' . __FILE__ . ' is damaged, it must be saved UTF-8 encoded!');
}

return array(
    '- delete field -'
        => '- Feld löschen -',
    '- new extra field -'
        => '- neues Zusatzfeld -',
    '- new group -'
        => '- neue Gruppe -',

    'About'
        => '?',
    'Activate the desired account role'
        => 'Das beantragte Benutzerrecht aktivieren',
    'Add a image'
        => 'Ein Bild hinzufügen',
    'Add category'
        => 'Kategorie hinzufügen',
    'Add extra field'
        => 'Zusatzfeld hinzufügen',
    'Add group'
        => 'Gruppe hinzufügen',
    'Add tag'
        => 'Markierung hinzufügen',
    'Add title'
        => 'Titel hinzufügen',
    'Admin Status'
        => 'Administrator Status',
    'At least we need one communication channel, so please tell us a email address, phone or a URL'
        => 'Wir benötigen mindestens einen Kommunikationsweg, bitte nennen Sie uns eine E-Mail Adresse, Telefonummer oder die URL der Homepage.',

    'by copying from a existing event'
        => 'durch Kopieren einer existierenden Veranstaltung',
    'by selecting a event group'
        => 'durch Auswahl einer Veranstaltungsgruppe',

    'Change account rights'
        => 'Änderung der Benutzerrechte',
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
    'company, institution or association'
        => 'Firma, Institution oder Verein',
    'Contact list'
        => 'Kontakte, Übersicht',
    'Contact type'
        => 'Kontakt Typ',
    'Costs'
        => 'Teilnahmegebühr',
    'Create a new contact'
        => 'Einen neuen Kontakt anlegen',
    'Create a new event'
        => 'Eine neue Veranstaltung erstellen',
    'Create a new extra field'
        => 'Ein neues Zusatzfeld anlegen',
    'Create a new group'
        => 'Eine neue Gruppe anlegen',
    'Create a new Location record'
        => 'Einen neuen Veranstaltungsort anlegen',
    'Create a new Organizer record'
        => 'Eine neue Veranstalter Adresse anlegen',

    // event data columns
    'description_long'
        => 'Beschreibung',
    'description_short'
        => 'Zusammenfassung',
    'description_title'
        => 'Titel',

    'Date'
        => 'Datum',
    'Date and Time'
        => 'Datum und Uhrzeit',
    'Deadline'
        => 'Anmeldeschluß',
    'delete this extra field'
        => 'dieses Zusatzfeld löschen',
    'Delete this image'
        => 'Dieses Bild löschen',
    'Description'
        => 'Beschreibung',
    'Description (translated)'
        => 'Beschreibung (übersetzt)',
    'Detected a kitEvent installation (Release: %release%) with %count% active or locked events.'
        => 'Es wurde eine kitEvent Installation (Release: %release%) mit %count% Veranstaltungen gefunden, die importiert werden können.',
    'Do not publish the event'
        => 'Veranstaltung nicht veröffentlichen',

    'Edit event'
        => 'Veranstaltung bearbeiten',
    'email usage'
        => 'Verwendung',

    // event data columns
    'event_costs'
        => 'Kosten',
    'event_date_from'
        => 'Datum von',
    'event_date_to'
        => 'Datum bis',
    'event_deadline'
        => 'Anmeldeschluß',
    'event_id'
        => 'ID',
    'event_participants_confirmed'
        => 'Tln. best.',
    'event_participants_max'
        => 'max. Tln.',
    'event_participants_total'
        => 'Anmeldungen',
    'event_publish_from'
        => 'Veröffentlichen ab',
    'event_publish_to'
        => 'Veröffentlichen bis',
    'event_status'
        => 'Status',
    'event_timestamp'
        => 'Zeitstempel',

    'Event'
        => 'Veranstaltung',
    'Event costs'
        => 'Eintrittspreis',
    'Event Date'
        => 'Veranstaltungsdatum',
    'Event date from'
        => 'Beginn der Veranstaltung',
    'Event date to'
        => 'Ende der Veranstaltung',
    'Event ID'
        => 'Veranstaltung ID',
    'Event Title'
        => 'Titel der Veranstaltung',
    'Extra field'
        => 'Zusatzfeld',
    'Extra fields'
        => 'Zusatzfelder',
    'Event list'
        => 'Veranstaltungen, Übersicht',
    'Event location'
        => 'Veranstaltungsort',
    'Event successfull updated'
        => 'Die Veranstaltung wurde aktualisiert',
    'Event url'
        => 'Veranstaltungs URL',

    'Field name'
        => 'Bezeichner',
    'Field name (translated)'
        => 'Bezeichner (übersetzt)',
    'Field type'
        => 'Feld Typ',
    'Float'
        => 'Dezimalzahl',
    'free of charge'
        => 'kostenlos',

    'go back'
        => 'Zurück',
    'Group'
        => 'Gruppe',
    'Group name'
        => 'Gruppen Bezeichner',
    'Group name (translated)'
        => 'Gruppen Bezeichner (übersetzt)',
    'Groups'
        => 'Gruppen',

    'Hello %name%'
        => 'Hallo %name%',

    'If you are prompted to login, please use your username and password'
        => 'Wenn Sie aufgefordert werden sich anzumelden, verwenden Sie bitte Ihren Benutzernamen und Ihr Passwort',
    'Import events from kitEvent'
        => 'Veranstaltungen aus kitEvent importieren',
    'Information about the Event extension'
        => 'Informationen über die Event Extension',
    'Int'
        => 'Ganzzahl',
    'Integer'
        => 'Ganzzahl',
    'Invalid key => value pair in the set[] parameter!'
        => 'Ungültiges Schlüssel => Wert Paar für den set[] Parameter!',
    'Invalid login'
        => 'Ungültiger Login, Benutzername oder Passwort falsch',
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
    'List of all registrations for events'
        => 'Übersicht über alle Anmeldungen zu Veranstaltungen',
    'Location'
        => 'Veranstaltungsort',
    'Location ID'
        => 'Veranstaltungsort ID',
    'Location Tags'
        => 'Veranstaltungsorte',
    'Locations'
        => 'Veranstaltungsorte',
    'Long description'
        => 'Langbeschreibung',

    'natural person'
        => 'Natürliche Person',
    'Name'
        => 'Bezeichner',
    'Name (translated)'
        => 'Bezeichner (übersetzt)',
    'New password'
        => 'Neues Passwort',
    'No results for this filter!'
        => 'Dieser Filter lieferte kein Ergebnis!',

    'Organizer'
        => 'Veranstalter',
    'Organizer ID'
        => 'Veranstalter ID',
    'Organizer Tags'
        => 'Veranstalter',

    'Permalink successfull changed'
        => 'Der Permanent Link wurde erfolgreich geändert',
    'personal email address'
        => 'persönliche E-Mail Adresse',
    'Please feel free to order a account.'
        => 'Fordern Sie ein Benutzerkonto an',
    'Please search for for a organizer or select the checkbox to create a new one.'
        => 'Bitte suchen Sie nach einem Veranstalter oder haken Sie die Checkbox an um einen neuen Veranstalter anzulegen.',
    'Please search for for a location or select the checkbox to create a new one.'
        => 'Bitte suchen Sie nach einem Veranstaltungsort oder haken Sie die Checkbox an um einen neuen Veranstaltungsort anzulegen.',
    'Please select at minimum one tag for the %type%.'
        => 'Bitte legen Sie mindestens eine Markierung für %type% fest!',
    'Please use the parameter set[] to set a configuration value.'
        => 'Bitte verwenden Sie den Paramter set[] um einen Konfigurationswert zu setzen!',
    'Participant'
        => 'Teilnehmer',
    'Participant Tags'
        => 'Teilnehmer',
    'Participants'
        => 'Teilnehmer',
    'Participants maximum'
        => 'Teilnehmer, max. Anzahl',
    'Participants canceled'
        => 'Teilnehmer, storniert',
    'Participants confirmed'
        => 'Teilnehmer, bestätigt',
    'Participants pending'
        => 'Teilnehmer, unbestätigt',
    'Participants total'
        => 'Teilnehmer, angemeldet',
    'Pictures'
        => 'Bilder',
    'Please check the event data and use one of the following action links'
        => 'Bitte prüfen Sie die Angaben zu der Veranstaltung und verwenden Sie anschließend einen der folgenden Aktions-Links',
    'Please define a permanent link in config.event.json. Without this link Event can not create permanent links or respond to user requests.'
        => 'Bitte definieren Sie einen permanenten Link in der config.event.json. Ohne diesen Link kann Event keine Verweise auf Veranstaltungen erzeugen oder auf Anfragen von Veranstaltungsteilnehmern reagieren.',
    'Please select action'
        => 'Bitte wählen Sie eine Aktion',
    'Please type in a long description with %minimum% characters at minimum.'
        => 'Bitte geben Sie eine Langbeschreibung mit einer Länge von mindestens %minimum% Zeichen ein.',
    'Please type in a short description with %minimum% characters at minimum.'
        => 'Bitte geben Sie eine Kurzbeschreibung mit einer Länge von mindestens %minimum% Zeichen ein.',
    'Please type in a title with %minimum% characters at minimum.'
        => 'Bitte geben Sie einen Titel mit einer Länge von mindestens %minimum% Zeichen ein.',
    'Proposes'
        => 'Vorschläge',
    'Proposed event: %event%'
        => 'Vorgeschlagene Veranstaltung: %event%',
    'Publish from'
        => 'Veröffentlichen ab',
    'Publish the event'
        => 'Veranstaltung veröffentlichen',
    'Publish to'
        => 'Veröffentlichen bis',

    'Received request'
        => 'Anfrage erhalten',
    'Registrations'
        => 'Anmeldungen',
    'regular email address of a company, institution or association'
        => 'offizielle E-Mail Adresse einer Firma, Einrichtung oder eines Verein',
    'Reject the desired account role'
        => 'Das gewünschte Benutzerrecht zurückweisen',
    'Reject this event'
        => 'Veranstaltung ablehnen',
    'ROLE_EVENT_ADMIN'
        => 'Benutzerrecht: Veranstaltungen als Administrator bearbeiten',
    'ROLE_EVENT_LOCATION'
        => 'Benutzerrecht: Veranstaltungen bearbeiten, die diesem Veranstaltungsort zugewiesen sind',
    'ROLE_EVENT_ORGANIZER'
        => 'Benutzerrecht: Veranstaltungen bearbeiten, die von diesem Veranstalter zugewiesen sind',
    'ROLE_EVENT_SUBMITTER'
        => 'Benutzerrecht: Veranstaltungen bearbeiten, die von diesem Benutzer vorgeschlagen wurden',

    'Search Location'
        => 'Veranstaltungsort suchen',
    'Search Organizer'
        => 'Veranstalter suchen',
    'Select account type'
        => 'Kontotyp wählen',
    'Select event group'
        => 'Veranstaltungsgruppe auswählen',
    'Short description'
        => 'Kurzbeschreibung',
    'Show detailed information'
        => 'Detailierte Informationen anzeigen',
    'Skipped kitEvent ID %event_id%: No valid value in %field%'
        => 'kitEvent ID <b>%event_id%</b> übersprungen: Ungültiger Wert in Feld %field%',
    'Start import from kitEvent'
        => 'Import aus kitEvent starten',
    'Submitter'
        => 'Übermittler',
    'Submitter ID'
        => 'Übermittler ID',
    'Submitter Status'
        => 'Übermittler Status',
    'Subscribe to event'
        => 'Zu der Veranstaltung anmelden',

    'Text - 256 characters'
        => 'Text - max. 256 Zeichen',
    'Text - HTML'
        => 'Text - HTML formatiert',
    'Text - plain'
        => 'Text - unformatiert',
    'Thank you, one of the admins will approve your request and contact you.'
        => 'Vielen Dank, ein Administrator wird Ihre Anfrage prüfen und sich mit Ihnen in Verbindung setzen.',
    'Thank you for proposing the following event'
        => 'Vielen Dank für Ihren Veranstaltungsvorschlag',
    'Thank you for your subscription. We have send you an email, please use the submitted confirmation link to confirm your email address and to activate your subscription!'
        => 'Vielen Dank für Ihre Anmeldung. Wir haben Ihnen eine E-Mail geschickt, bitte benutzen Sie den enthaltenen Bestätigungslink um Ihre E-Mail Adresse zu bestätigen und die Anmeldung zu aktivieren.',
    'Thank you for your subscription, we have send you a receipt at your email address.'
        => 'Vielen Dank für Ihre Anmeldung, wir haben Ihnen eine Bestätigung an Ihre E-Mail Adresse gesendet.',
    'The action link was successfull executed'
        => 'Der Aktionslink wurde erfolgreich ausgeführt',
    'The change of your account rights is approved by admin'
        => 'Die Änderung Ihrer Benutzerrechte wurde durch den Administrator genehmigt',
    'The change of your account rights is rejected by admin'
        => 'Die Änderung Ihrer Benutzerrechte wurde durch den Administrator abgelehnt',
    'The contact record was successfull updated.'
        => 'Der Adressdatensatz wurde aktualisiert.',
    'The deadline ends after the event start date!'
        => 'Der Anmeldeschluß liegt nach dem Beginn der Veranstaltung!',
    'The email address %email% is associated with a company contact record. At the moment you can only subscribe to a event with your personal email address!'
        => 'Die E-Mail Adresse %email% ist einer Firma oder Institution zugeordnet. Zur Zeit können Sie sich jedoch nur mit einer persönlichen E-Mail Adresse zu einer Veranstaltung anmelden.',
    'The email address %email% is not registered. We can only create a account for you if there was already a interaction, i.e. you have proposed a event. If you represent an organizer or a location and your public email address is not registered, please contact the administrator.'
        => 'Die E-Mail Adresse %email% ist nicht registriert. Wir können nur dann ein Benutzerkonto für Sie anlegen, wenn bereits eine Interaktion stattgefunden hat und Sie z.B. eine Veranstaltung vorgeschlagen haben. Falls Sie einen Veranstalter oder einen Veranstaltungsort vertreten und Ihre öffentliche E-Mail Adresse nicht registriert ist, wenden Sie sich bitte an den Administrator.',
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
    'The field list is empty, please define a extra field!'
        => 'Es wurden noch keine Zusatzfelder definiert, bitte erstellen Sie ein neues Zusatzfeld!',
    'The group list is empty, please define a group!'
        => 'Es existieren keine Gruppen, bitte legen Sie eine Gruppe an!',
    'The identifier %identifier% already exists!'
        => 'Der Bezeichner %identifier% existiert bereits!',
    'The image <b>%image%</b> has been added to the event.'
        => 'Der Veranstaltung wurde das Bild <b>%image%</b> hinzugefügt.',
    'The image with the ID %image_id% was successfull deleted.'
        => 'Das Bild mit der ID %image_id% wurde erfolgreich gelöscht.',
    'The publishing date ends before the event starts, this is not allowed!'
        => 'Der Veröffentlichungszeitraum endet vor dem Beginn der Veranstaltung, dies ist nicht gewünscht!',
    'The publishing date is behind the event start date!'
        => 'Das Veröffentlichungsdatum liegt nach dem Veranstaltungsdatum!',
    'The record with the ID %id% does not exists!'
        => 'Der Datensatz mit der ID %id% existiert nicht!',
    'The record with the ID %id% was successfull deleted.'
        => 'Der Datensatz mit der ID %id% wurde gelöscht.',
    'The record with the ID %id% was successfull inserted.'
        => 'Der Datensatz mit der ID %id% wurde erfolgreich eingefügt.',
    'The record with the ID %id% was successfull updated.'
        => 'Der Datensatz mit der ID %id% wurde erfolgreich aktualisiert.',
    'The status of your address record is actually %status%, so we can not accept your subscription. Please contact the <a href="mailto:%email%">webmaster</a>.'
        => 'Der Status Ihres Adressdatensatz ist zur Zeit auf %status% gesetzt, wir können Ihre Anmeldung daher nicht entgegennehmen. Bitte nehmen Sie Kontakt mit dem <a href="mailto:%email%">Webmaster</a> auf, um die Situation zu klären.',
    'The submitted GUID %guid% does not exists.'
        => 'Die übermittelte GUID %guid% existiert nicht!',
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
    'The view <b>%view%</b> does not exists!'
        => 'Die Ansicht (view) <b>%view%</b> existiert nicht!',
    'There exists no kitEvent installation at the parent CMS!'
        => 'Es wurde keine kitEvent Installation in dem übergeordeneten Content Management System gefunden!',
    'There exists no locations who fits to the search term %search%'
        => 'Es wurde kein Veranstaltungsort gefunden, der zu dem Suchbegriff <i>%search%</i> passt.',
    'There exists no organizer who fits to the search term %search%'
        => 'Es wurde kein Veranstalter gefunden, der zu dem Suchbegriff <i>%search%</i> passt.',
    'This activation link was already used and is no longer valid!'
        => 'Dieser Aktivierungslink wurde bereits verwendet und ist nicht mehr gültig!',
    'This extra field is used in the event group %group%. First remove the extra field from the event group.'
        => 'Dieses Zusatzfeld wird in der Veranstaltungs Gruppe %group% verwendet. Sie müssen das Zusatzfeld zunächst aus der Gruppe entfernen.',
    'This user has a account but was never in contact in context with events'
        => 'Dieser Benutzer verfügt über ein Benutzerkonto, war jedoch im Zusammenhang mit Veranstaltungen noch nie im Kontakt',
    'This user has a contact record but was never in contact in context with events'
        => 'Dieser Benutzer verfügt über einen Kontaktdatensatz, war jedoch im Zusammenhang mit Veranstaltungen noch nie im Kontakt',
    'Type'
        => 'Typ',

    'unlimited'
        => 'unbegrenzt',
    'Using qrcode[] is not enabled in config.event.json!'
        => 'qrcode[] ist nicht in der config.event.json freigegeben!',

    'We have send you a new password, please check your email account'
        => 'Wir haben Ihnen ein neues Passwort an Ihre E-Mail Adresse gesendet, bitte prüfen Sie Ihren Posteingang',
    'We send you a new password to your email address.'
        => 'Wir senden Ihnen ein neues Passwort an Ihre E-Mail Adresse.',

    'You are authenticated but not allowed to edit this event. Please contact the admin if you are of the mind that you should be able for.'
        => 'Sie sind korrekt angemeldet jedoch nicht berechtigt diese Veranstaltung zu bearbeiten. Bitte wenden Sie sich an den Administrator um die erforderliche Berechtigung zu erhalten.',
    'Your are not authenticated, please login!'
        => 'Sie sind nicht berechtigt auf diese Inhalte zuzugreifen, bitte melden Sie sich an!',
    'Your contact record is locked, so we can not perform any action. Please contact the administrator'
        => 'Ihr Kontakt Datensatz ist gesperrt, wir können keine Aktion durchführen. Bitte wenden Sie sich an den Administrator.',
    'You have already subscribed to this Event at %datetime%, you can not subscribe again.'
        => 'Sie haben sich am %datetime% bereits zu dieser Veranstaltung angemeldet und können sich deshalb nicht erneut anmelden.',
    'You have already the right to edit events (%role%). Please contact the administrator if you want to change or extend your account rights'
        => 'Sie verfügen bereits über ein Benutzerkonto und das Recht Veranstaltungen zu bearbeiten (%role%). Bitte wenden Sie sich an den Administrator, wenn Sie geänderte oder erweiterte Benutzerrechte benötigen.',
    'You have now the additional right to: "%role%"'
        => 'Sie verfügen jetzt über das zusätzliche Recht: "%role%"',
    'You have selected <i>Company, Institution or Association</i> as contact type, so please give us the name'
        => 'Sie haben <i>Firma, Institution oder Verein</i> als Kontakt Typ angegeben, bitte nennen Sie uns den Namen der Einrichtung.',
    'You have selected <i>natural person</i> as contact type, so please give us the last name of the person.'
        => 'Sie haben <i>natürliche Person</i> als Kontakt Typ gewählt, bitte nennen Sie uns den Nachnamen der Person.',
    'You need a account if you want to edit events. In general we will give accounts to all event organizers, locations and persons which submit events frequently.'
        => 'Sie benötigen ein Benutzerkonto um Veranstaltungen ändern und ergänzen zu können. Im Allgemeinen erhalten alle Veranstalter, Veranstaltungsorte sowie Personen, die regelmäßig Veranstaltungen eintragen, einen Zugang von uns.',
    'Your subscription for the event %event% is already confirmed.'
        => 'Ihre Anmeldung für die Veranstaltung %event% wurde bereits bestätigt.',

    'zip'
        => 'PLZ'
);
