<?php

/**
 * kitFramework::Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
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
  '%count% hits for the search term </i>%search%</i>.'
    => '%count% Treffer für den Suchbegriff <i>%search%</i>.',
  '%hits% hits for the search of <strong>%search%</strong>'
    => '%hits% Treffer für die Suche nach %search%',
  '<strong>%number%</strong> is not a valid phone number.'
    => '<strong>%number%</strong> ist keine gültige Telefonnummer.',
  '%type% Import'
    => '%type% Import',
  '- delete field -'
    => '- Feld löschen -',
  '- new contact -'
    => '- neuer Kontakt -',
  '- no category -'
    => '- keine Kategorie -',
  '- no tags -'
    => '- keine Schlagwörter -',
  '- not assigned -'
    => '- nicht zugeordnet -',
  '- select category (optional) -'
    => '- Kategorie auswählen (optional) -',
  '- select country (optional) -'
    => '- Land auswählen (optional) -',
  'Active'
    => 'Aktiv',
  'Add a new category'
    => 'Eine neue Kategorie erstellen',
  'Add a new contact'
    => 'Einen neuen Kontakt erstellen',
  'Add a new extra field'
    => 'Ein neues Zusatzfeld erstellen',
  'Add a new tag'
    => 'Eine neue Markierung (#tag) erstellen',
  'Add a new title'
    => 'Einen neuen Titel hinzufügen',
  'Add extra field'
    => 'Zusatzfeld hinzufügen',
  'Additional'
    => 'Zusatz',
  'Address'
    => 'Adresse',
  'Address area'
    => 'Bezirk, Region',
  'Address billing'
    => 'Rechungsadresse',
  'Address billing area'
    => 'Bezirk, Region',
  'Address billing city'
    => 'Stadt',
  'Address billing country code'
    => 'Land',
  'Address billing state'
    => 'Bundesland',
  'Address billing street'
    => 'Straße',
  'Address billing zip'
    => 'PLZ',
  'Address business'
    => 'Geschäftsadresse',
  'Address business area'
    => 'Bezirk, Region',
  'Address business city'
    => 'Stadt',
  'Address business country'
    => 'Land',
  'Address business country code'
    => 'Land',
  'Address business id'
    => 'ID',
  'Address business state'
    => 'Bundesland',
  'Address business street'
    => 'Straße',
  'Address business zip'
    => 'PLZ',
  'Address city'
    => 'Stadt',
  'Address country'
    => 'Land',
  'Address country code'
    => 'Land',
  'Address delivery'
    => 'Lieferadresse',
  'Address delivery area'
    => 'Bezirk, Region',
  'Address delivery city'
    => 'Stadt',
  'Address delivery country'
    => 'Land',
  'Address delivery country code'
    => 'Land',
  'Address delivery id'
    => 'ID',
  'Address delivery state'
    => 'Bundesland',
  'Address delivery street'
    => 'Straße',
  'Address delivery zip'
    => 'PLZ',
  'Address id'
    => 'ID',
  'Address private'
    => 'Adresse',
  'Address private area'
    => 'Bezirk, Region',
  'Address private city'
    => 'Stadt',
  'Address private country code'
    => 'Land',
  'Address private state'
    => 'Bundesland',
  'Address private street'
    => 'Straße',
  'Address private zip'
    => 'PLZ',
  'Address state'
    => 'Bundesland',
  'Address street'
    => 'Straße',
  'Address zip'
    => 'PLZ',
  'Admin'
    => 'Administrator',
  'Allowed characters for the %identifier% identifier are only A-Z, 0-9 and the Underscore. The identifier will be always converted to uppercase.'
    => 'Erlaubte Zeichen für den %identifier% Bezeichner sind A-Z, 0-9 und der Unterstrich. Der Bezeichner wird stets in Großbuchstaben umgewandelt.',
  'Archived'
    => 'Archiviert',
  'Area'
    => 'Bezirk, Region',
  'Assign the fields'
    => 'Datenfelder zuordnen',
  'AT'
    => 'Österreich',
  'Back'
    => 'Zurück',
  'baron'
    => 'Baron',
  'Birthday'
    => 'Geburtstag',
  'Browse'
    => 'Durchsuchen',
  'Business address'
    => 'Geschäftsadresse',
  'Can not create GUID, submission aborted, please contact the webmaster.'
    => 'Konnte die Guid nicht erzeugen, die Übermittlung wurde abgebrochen. Bitte nehmen Sie Kontakt mit dem Webmaster auf!',
  'Can\'t delete the Adress with the ID %address_id% because it is used as primary address.'
    => 'Die Adresse mit der ID %address_id% kann nicht gelöscht werden, da sie als primäre Adresse für diesen Kontakt verwendet wird.',
  'Can\'t delete the Note with the ID %note_id% because it is used as primary note for this contact.'
    => 'Die Notiz mit der ID %note_id% kann nicht gelöscht werden, da sie als primäre Information für den Kontakt verwendet wird.',
  'Can\'t read the contact with the ID %contact_id% - it is possibly deleted.'
    => 'Der Kontakt Datensatz mit der ID %contact_id% konnte nicht gelesen werden, er wurde möglicher Weise gelöscht.',
  'Can\'t send mail to %recipients%.'
    => 'Konnte keine E-Mail an den/die Empfänger %recipients% senden!',
  'Categories'
    => 'Kategorien',
  'Category'
    => 'Kategorie',
  'Category access'
    => 'Kategorie Zugriff',
  'Category definition'
    => 'Kategorie Definition',
  'Category description'
    => 'Beschreibung',
  'Category description (translated)'
    => 'Kategorie Beschreibung (Übersetzung)',
  'Category extra fields'
    => 'Zusatzfelder',
  'Category image'
    => 'Abbildung',
  'Category name'
    => 'Kategorie',
  'Category name (translated)'
    => 'Kategorie Bezeichner (Übersetzung)',
  'Category type access'
    => 'Kategorie Zugriff',
  'Category type description'
    => 'Beschreibung',
  'Category type id'
    => 'Kategorie',
  'Category type name'
    => 'Bezeichnung',
  'Category type target url'
    => 'Ziel URL',
  'Cell'
    => 'Mobilfunk',
  'Cell id'
    => 'ID',
  'CH'
    => 'Schweiz',
  'City'
    => 'Stadt',
  'Click to select the %type% file for import'
    => '%type% Datei für den Import auswählen',
  'Click to sort column ascending'
    => 'Anklicken um die Spalte aufsteigend zu sortieren',
  'Click to sort column descending'
    => 'Anklicken um die Spalte absteigend zu sortieren',
  'Communication'
    => 'Kommunikation',
  'Communication cell'
    => 'Mobil',
  'Communication email'
    => 'E-Mail',
  'Communication fax'
    => 'Telefax',
  'Communication phone'
    => 'Telefon',
  'Communication url'
    => 'URL',
  'Company'
    => 'Firma',
  'Company additional'
    => 'Zusatz',
  'Company additional 2'
    => 'Zusatz',
  'Company additional 3'
    => 'Zusatz',
  'Company department'
    => 'Abteilung',
  'Company id'
    => 'ID',
  'Company name'
    => 'Firma',
  'Contact'
    => 'Kontakt',
  'Contact Administration - About'
    => 'Kontakt Verwaltung - Über',
  'Contact category'
    => 'Kategorie',
  'Contact data submitted'
    => 'Kontaktdaten übermittelt',
  'Contact Export'
    => 'Kontakt Export',
  'Contact id'
    => 'ID',
  'Contact Import'
    => 'Kontakt Import',
  'Contact list'
    => 'Kontaktliste',
  'Contact login'
    => 'Kontakt Anmeldename',
  'Contact name'
    => 'Kontakt Bezeichner',
  'Contact need a unique identifier for each record. By default this is the email address but it can also the contact login. For this reason you must assign the field communication_email or contact_login.'
    => 'Contact benötigt eine eindeutige Kennung für jeden Datensatz. Normalerweise ist die E-Mail Adresse aber es kann auch der Anmeldename (Login) verwendet werden. Aus diesem Grund müssen Sie das Feld <em>communication_email</em> oder <em>contact_login</em> zuordnen.',
  'Contact note'
    => 'Notiz',
  'Contact pending'
    => 'Kontaktdaten werden geprüft',
  'Contact published'
    => 'Kontaktdaten veröffentlicht',
  'Contact record'
    => 'Kontaktdatensatz',
  'Contact record confirmed'
    => 'Kontaktdatensatz bestätigt',
  'Contact records successfull exported as <a href="%url%">%file_name%</a>. Please <a href="%remove%">remove the file</a> after download.'
    => 'Kontaktdatensätze erfolgreich exportiert als <a href="%url%">%file_name%</a>. Bitte <a href="%remove%">entfernen Sie die Datei</a> nach dem Herunterladen.',
  'Contact rejected'
    => 'Zurückgewiesen',
  'Contact search'
    => 'Suche',
  'Contact settings'
    => 'Kontakt Information',
  'Contact since'
    => 'Kontakt seit',
  'Contact status'
    => 'Status',
  'Contact tags'
    => 'Markierungen',
  'Contact timestamp'
    => 'Letzte Änderung',
  'Contact type'
    => 'Kontakttyp',
  'Contact Type: %type%'
    => 'Kontakt Typ: %type%',
  'Contacts'
    => 'Kontakte',
  'Country'
    => 'Land',
  'Create a new contact'
    => 'Einen neuen Kontakt anlegen',
  'Create contact'
    => 'Kontakt anlegen',
  'Create or edit a contact record'
    => 'Kontakt Datensatz anlegen oder bearbeiten',
  'Create or edit category'
    => 'Kategorie erstellen oder bearbeiten',
  'Create or edit contact'
    => 'Kontakt erstellen oder bearbeiten',
  'Create or edit extra field'
    => 'Zusatzfeld erstellen oder bearbeiten',
  'Create or edit tag'
    => 'Markierung erstellen oder bearbeiten',
  'Create or edit title'
    => 'Anrede erstellen oder bearbeiten',
  'Customer'
    => 'Kunde',
  'Customer relationship management for the kitFramework'
    => 'Kontakt- und Adressverwaltung (CRM) für das kitFramework',
  'Delete'
    => 'Löschen',
  'Deleted'
    => 'Gelöscht',
  'Delimiter'
    => 'Trennzeichen',
  'Delivery address'
    => 'Lieferadresse',
  'Description'
    => 'Beschreibung',
  'Description (translated)'
    => 'Beschreibung (übersetzt)',
  'Detected a KeepInTouch installation (Release: %release%) with %count% active or locked contacts.'
    => 'Es wurde eine KeepInTouch Installation (Release: %release%) mit %count% aktiven oder gesperrten Kontakten gefunden.',
  'Determine contact type'
    => 'Kontakt Typ festlegen',
  'Determine default values'
    => 'Vorgabewerte festlegen',
  'doc'
    => 'Dr.',
  'doctor'
    => 'Doktor',
  'Don\'t understand the value %value% for the entry: command->register->publish->activate, please check the configuration!'
    => 'Verstehen den Wert %value% für den Eintrag: command->register->publish->activate nicht, bitte prüfen Sie die Konfiguration!',
  'earl'
    => 'Graf',
  'Edit categories'
    => 'Kategorien bearbeiten',
  'Edit category'
    => 'Kategorie bearbeiten',
  'Edit contact'
    => 'Kontakt bearbeiten',
  'Edit extra field'
    => 'Zusatzfeld bearbeiten',
  'Edit extra fields'
    => 'Zusatzfelder bearbeiten',
  'Edit tag'
    => 'Markierung bearbeiten',
  'Edit tags'
    => 'Markierungen bearbeiten',
  'Edit title'
    => 'Titel bearbeiten',
  'Edit titles'
    => 'Titel bearbeiten',
  'Email id'
    => 'ID',
  'Enclosures'
    => 'Einfassungen',
  'Encoding'
    => 'Kodierung',
  'Execute import'
    => 'Import durchführen',
  'Export'
    => 'Export',
  'Export as'
    => 'Exportieren im Format',
  'Export contact records'
    => 'Kontaktdatensätze exportieren',
  'Export Contact records in CSV or Excel file format'
    => 'Export von Kontakt Datensätzen im CSV oder Excel Dateiformat',
  'Extra field'
    => 'Zusatzfeld',
  'Extra field definition'
    => 'Zusatzfelder Definition',
  'Extra fields'
    => 'Zusatzfelder',
  'Failed to send a email with the subject <b>%subject%</b> to the addresses: <b>%failed%</b>.'
    => 'Eine E-Mail mit dem Betreff <b>%subject%</b> konnte an die folgenden Adressaten nicht übermittelt werden: <b>%failed%</b>.',
  'Fatal: Can not import contact record because the email address %email% is invalid.'
    => 'Fatal: Kann den Kontaktdatensatz nicht importieren, da die E-Mail Adresse %email% ungültig ist!',
  'Fax'
    => 'Fax',
  'Fax id'
    => 'ID',
  'Female'
    => 'Frau',
  'Field name'
    => 'Bezeichner',
  'Field name (translated)'
    => 'Bezeichner (übersetzt)',
  'Field type'
    => 'Feld Typ',
  'Fields of type `select`, `radio` or `checkbox` need one or more values defined as array in `choices`!'
    => 'Felder vom Typ `select`, `radio` oder `checkbox` benötigen einen oder mehrere Werte, die als Array in `choices` übergeben werden!',
  'File %file% successfull removed.'
    => 'Datei %file% erfolgreich entfernt.',
  'First name'
    => 'Vorname',
  'Forgotten'
    => 'Vergessen',
  'Gender'
    => 'Geschlecht',
  'I accept that this software is provided under <a href="http://opensource.org/licenses/MIT" target="_blank">MIT License</a>.'
    => 'Ich akzeptiere, dass diese Software unter der <a href="http://opensource.org/licenses/MIT" target="_blank">MIT Lizenz</a> veröffentlicht wurde.',
  'I\'m a sample header'
    => 'Ich bin ein Beispiel für eine Überschrift',
  'Identifier'
    => 'Bezeichner',
  'If you are the owner of the contact record you can change or update the data, please login. If you have never got any account information please select "Forgot password?'
    => 'Falls Sie der Inhaber des Kontaktdatensatz sind können Sie die Daten jederzeit aktualisieren oder ändern, bitte melden Sie sich an. Falls Sie bisher keine Zugangsdaten erhalten haben, wählen Sie bitte "Haben Sie ihr Passwort vergessen?".',
  'If you have never got a password or still forgot it, you can order a link to create a new one. Just type in the email address which is assigned to the contact record you want zu change or update and we will send youn an email.'
    => 'Falls Sie bisher kein Passwort erhalten oder das Passwort verloren haben, können Sie einen Link anfordern um ein neues Passwort zu erstellen. Geben Sie einfach die E-Mail Adresse an, die dem Kontaktdatensatz zugeordnet ist den Sie ändern oder aktualisieren möchten, wir senden Ihnen einen Link zu.',
  'Import'
    => 'Import',
  'Import address and contact records from KeepInTouch or as CSV, Excel or Open Data file format.'
    => 'Importieren von Adress- und Kontaktdatensätzen aus KeepInTouch oder im CSV, Excel oder Open Data Dateiformat',
  'Import contact records'
    => 'Kontaktdaten importieren',
  'Import contacts from KeepInTouch (KIT)'
    => 'Kontakte aus KeepInTouch (KIT) importieren',
  'Import fields'
    => 'Import Datenfelder',
  'Import file'
    => 'Import Datei',
  'Import from'
    => 'Importieren aus',
  'Import type'
    => 'Import Typ',
  'Information about the Contact extension'
    => 'Information über kitFramework Contact',
  'Inserted the new contact with the ID %contact_id%.'
    => 'Es wurde ein neuer Kontakt mit der ID %contact_id% hinzugefügt',
  'Intern'
    => 'Intern',
  'Invalid Activation, missing the GUID!'
    => 'Ungülitge Aktivierung, vermisse die GUID!',
  'Invalid GUID, can not evaluate the desired account!'
    => 'Ungültiger Aktivierungslink! Die GUID wurde möglichweise bereits verwendet.',
  'Last name'
    => 'Nachname',
  'List of all available contacts'
    => 'Liste aller verfügbaren Kontakte',
  'List of available categories'
    => 'Liste aller verfügbaren Kategorien',
  'List of available extra fields'
    => 'Liste der verfügbaren Zusatzfelder',
  'List of available tags'
    => 'Liste der verfügbaren Markierungen',
  'Locked'
    => 'Gesperrt',
  'Long name'
    => 'Langbezeichnung',
  'Long name (translated)'
    => 'Langbezeichnung (Übersetzung)',
  'Male'
    => 'Herr',
  'mandatory field'
    => 'Pflichtfeld',
  'Merchant'
    => 'Händler',
  'Missing one or more keys in the field definition array! At least are needed: predefined, visible, hidden, required, readonly'
    => 'Vermisse einen oder mehrere Schlüssel in der Feld Definition! Es werden mindestens benötigt: predefined, visible, hidden, required und readonly.',
  'Missing the %identifier%! The ID should be set to -1 if you insert a new record.'
    => 'Das Feld <b>%identifier%</b> fehlt! Diese ID sollte auf -1 gesetzt sein, wenn Sie einen neuen Datensatz einfügen möchten.',
  'Missing the `name` field in the definition!'
    => 'Bei den Definitionen für die Eingabefelder ist die Angabe des `name` Feld Pflicht!',
  'Missing the `type` field in the definition!'
    => 'Bei den Definitionen für die Eingabefelder ist die Angabe des `type` Feld Pflicht!',
  'Missing the contact block! Can\'t insert the new record!'
    => 'Vermissen den Kontakt Block, kann keinen neuen Datensatz einfügen!',
  'Missing the field <strong>%field%</strong> in data record!'
    => 'Vermisse das Feld <strong>%field%</strong> im Datensatz!',
  'Missing the field `extra_type_name`'
    => 'Vermisse das Feld `extra_type_name`',
  'Missing the field `name` in the `form.json` tag definition!'
    => 'Vermisse das Feld `name` in der `form.json` Schlagwort Definition!',
  'Missing the field definitions in `form.json`!'
    => 'In der `form.json` wurden keine Feld Definitionen gefunden!',
  'Missing the handling for the field type `%type%`, please contact the support!'
    => 'Vermisse die Handhabung für den Feld Typ `%type%`, bitte kontaktieren Sie den Support!',
  'Missing the key %field_name%, it must always set and not empty!'
    => 'Der Schlüssel %field_name% muss immer gesetzt werden und darf nicht leer sein!',
  'Missing the parameter <b>%parameter%</b>, please check the kitCommand expression!'
    => 'Vermisse den Parameter <b>%parameter%</b>, bitte prüfen Sie den kitCommand Ausdruck!',
  'Missing the parameter `form`!'
    => 'Vermissen den Parameter <em>form</em>!',
  'Name'
    => 'Bezeichner',
  'Name (translated)'
    => 'Bezeichnung (übersetzt)',
  'Nick name'
    => 'Spitzname',
  'No category'
    => 'Keine Kategorie zugeordnet',
  'no extra field assigned'
    => 'kein Zusatzfeld zugeordnet',
  'No hits for the search of %search%'
    => 'Keine Treffer für die Suche nach %search%',
  'No hits for the search term <i>%search%</i>!'
    => 'Keine Treffer für den Suchbegriff <i>%search%</i>!',
  'Note'
    => 'Notiz',
  'Note content'
    => 'Notiz',
  'Note id'
    => 'ID',
  'Nothing to do ...'
    => 'Nichts zu tun ...',
  'Organization'
    => 'Organisation',
  'Overview'
    => 'Übersicht',
  'Pending'
    => 'Ungeklärt',
  'Person'
    => 'Person',
  'Person birthday'
    => 'Geburtstag',
  'Person first name'
    => 'Vorname',
  'Person gender'
    => 'Anrede',
  'Person id'
    => 'ID',
  'Person last name'
    => 'Nachname',
  'Person nick name'
    => 'Spitzname',
  'Person title'
    => 'Titel',
  'Phone'
    => 'Telefon',
  'Phone id'
    => 'ID',
  'Please define a short name for the title!'
    => 'Bitte legen Sie eine Kurzbezeichnung für den Titel fest!',
  'Please select the target file format to export the kitFramework Contact records: <a href="%xlsx%">XLSX (Excel)</a> or <a href="%csv%">CSV (Text)</a>.'
    => 'Bitte wählen Sie das Ausgabeformat um die kitFramework Contact Datensätze zu exportieren: <a href="%xlsx%">XLSX (Excel)</a> oder <a href="%csv%">CSV (Text)</a>.',
  'Please specify a search term!'
    => 'Bitte geben Sie einen Suchbegriff ein!',
  'Please use a search term to reduce the hits!'
    => 'Bitte verwenden Sie einen Suchbegriff um die Anzahl der Treffer zu verringern!',
  'Please use the parameter <em>categories[]</em> to specify at minimum one category with PUBLIC access!'
    => 'Bitte verwenden Sie den Parameter <em>categories[]</em> und geben Sie mindestens eine Kategorie mit <em>PUBLIC</em> Zugriff an!',
  'prof'
    => 'Prof.',
  'professor'
    => 'Professor',
  'Public'
    => 'Öffentlich',
  'Publish a contact'
    => 'Kontaktdaten freigeben',
  'Register a contact'
    => 'Kontaktdaten bestätigen',
  'Register a public contact record'
    => 'Öffentlichen Kontaktdatensatz registrieren',
  'required'
    => 'erforderlich',
  'Search'
    => 'Suche',
  'Search contact'
    => 'Kontakt suchen',
  'Select category'
    => 'Kategorie festlegen',
  'Select contact'
    => 'Kontakt auswählen',
  'Select contact type'
    => 'Kontakt Typ',
  'Select tags'
    => 'Markierungen auswählen',
  'Select type'
    => 'Typ auswählen',
  'Short name'
    => 'Kurzbezeichnung',
  'Short name (translated)'
    => 'Kurzbezeichnung (Übersetzung)',
  'Skipped contact record %login% because it ist deleted or locked for any reason!'
    => 'Der Kontaktdatensatz %login% wurde übersprungen - dieser ist gelöscht oder aus irgendeinem Grund gesperrt!',
  'Sorry, but there occured a problem while processing the form. We have informed the webmaster.'
    => 'Entschuldigung, während der Verarbeitung des Formulars ist ein Problem aufgetreten. Wir haben den Webmaster informiert.',
  'Sorry, but we have a problem. Please contact the webmaster and tell him to check the status of the email address %email%.'
    => 'Entschuldigung, wir haben ein Problem. Bitte wenden Sie sich an den Webmaster und bitten Sie ihn, den Status der E-Mail Adresse %email% zu überprüfen!',
  'Start export'
    => 'Export starten',
  'Start import'
    => 'Import starten',
  'Start import from KeepInTouch'
    => 'Den Import aus KeepInTouch starten',
  'State'
    => 'Bundesland',
  'Stay in touch, read our newsletter!'
    => 'Bleiben Sie mit uns in Kontakt, abonnieren Sie unseren Newsletter!',
  'Street'
    => 'Straße',
  'Submission from form %form%'
    => 'Übermittlung vom Formular %form%',
  'Tag'
    => 'Markierung',
  'Tag (translated)'
    => 'Markierung (übersetzt)',
  'Tag definition'
    => '#tag Definition',
  'Tag description'
    => '#Hashtag Beschreibung',
  'Tag name'
    => 'Bezeichner',
  'Tag type id'
    => 'ID',
  'Tags'
    => 'Markierungen',
  'Target URL'
    => 'Ziel URL',
  'The #tag %tag_name% does not exists!'
    => 'Das #Schlagwort %tagname% existiert nicht!',
  'The %entry% entry with the ID %id% was not processed, there exists no fitting record for comparison!'
    => 'Der Eintrag %entry% mit der ID %id% wurde nicht aktualisiert, es wurde kein passender Eintrag in der Tabelle gefunden!',
  'The %type% entry %value% is marked for primary communication and can not removed!'
    => 'Der Typ %type% mit dem Wert %value% ist für die primäre Kommunikation mit dem Kontakt festgelegt und kann nicht gelöscht werden!',
  'The action <b>%action%</b> is unknown, please check the parameters for the kitCommand!'
    => 'Der Wert <strong>%action%</strong> für den Parameter <em>action</em> ist unbekannt, bitte prüfen Sie Ihre Angaben für das kitCommand!',
  'The Address with the ID %address_id% was successfull deleted.'
    => 'Die Adresse mit der ID %address_id% wurde erfolgreich gelöscht.',
  'The category %category% does not exists!'
    => 'Die Kategorie %category% existiert nicht!',
  'The category type with the ID %category_id% does not exists!'
    => 'Die Kategorie mit der ID %category_id% existiert nicht!',
  'The communication entry %communication% was successfull deleted.'
    => 'Der Kommunikationseintrag <b>%communication%</b> wurde gelöscht.',
  'The COMMUNICATION TYPE %type% does not exists!'
    => 'Der Kommunikationstyp <b>%type%</b> existiert nicht, bitte prüfen Sie Ihre Eingabe!',
  'The COMMUNICATION TYPE must be set!'
    => 'Das Feld <b>communication type</b> muss gesetzt sein!',
  'The COMMUNICATION USAGE %usage% does not exists!'
    => 'Die Kommunikationsverwendung <b>%usage%</b> existiert nicht, bitte prüfen Sie Ihre Eingabe!',
  'The COMMUNICATION USAGE must be set!'
    => 'Das Feld <b>communication usage</b> muss gesetzt sein!',
  'The COMMUNICATION VALUE should not be empty!'
    => 'Der Kommunikationswert darf nicht leer oder Null sein!',
  'The contact block must be set always!'
    => 'Der <em>contact</em> Block muss immer gesetzt sein!',
  'The contact list is empty.'
    => 'Die Kontaktliste enthält keine Einträge!',
  'The contact login must be set!'
    => 'Der Kontakt <b>Login</b> muss gesetzt sein!',
  'The contact name %name% already exists! The update has still executed, please check if you really want this duplicate name.'
    => 'Der Kontakt Name <b>%name%</b> wird bereits verwendet! Der Datensatz wurde trotzdem aktualisiert, bitte prüfen Sie ob sie den doppelten Eintrag beibehalten möchten.',
  'The contact name must be set!'
    => 'Der Kontaktbezeichner muss gesetzt sein!',
  'The contact process has not returned a status message'
    => 'Der Prozess hat keine Statusmeldung zurückgegeben.',
  'The contact record is now published, the submitter has received an email with further information.'
    => 'Der Kontaktdatensatz wurde veröffentlicht, dem Übermittler wurde eine E-Mail mit weiteren Informationen zugesendet.',
  'The contact record was not changed!'
    => 'Der Kontakt Datensatz wurde nicht geändert.',
  'The contact was rejected and an email send to the submitter'
    => 'Der Kontakt wurde zurückgewiesen und eine E-Mail an den Übermittler gesendet.',
  'The contact with the ID %contact_id% does not exists!'
    => 'Es existiert kein Kontakt Datensatz mit der ID %contact_id%!',
  'The contact with the ID %contact_id% was successfull updated.'
    => 'Der Kontakt mit der ID %contact_id% wurde erfolgreich aktualisiert.',
  'The contact_type must be always set (%contact_types%).'
    => 'Der Kontakt Typ muss immer gesetzt sein, mögliche Werte: %contact_types%.',
  'The country code %country_code% does not exists!'
    => 'Der Ländercode <b>%country_code%</b> existiert nicht!',
  'The email address %email% is not valid, please check your input!'
    => 'Die E-Mail Adresse %email% ist nicht gültig, bitte überprüfen Sie Ihre Eingabe!',
  'The extra field %field% is no longer assigned to the category %category%'
    => 'Das Zusatzfeld %field% ist nicht mehr der Kategorie %category% zugeordnet!',
  'The extra field %field% is now assigned to the category %category%'
    => 'Das Zusatzfeld %field% ist jetzt der Kategorie %category% zugeordnet und kann verwendet werden!',
  'The field %field% can not be empty!'
    => 'Das Feld %field% darf nicht leer sein!',
  'The field %field% is required, please check your input!'
    => 'Das Feld %field% wird benötigt, bitte prüfen Sie Ihre Eingabe!',
  'The field %field% is unknown, please check the configuration!'
    => 'Das Feld %field% ist unbekannt, bitte prüfen Sie die Konfiguration!',
  'The field list is empty, please define a extra field!'
    => 'Es wurden noch keine Zusatzfelder definiert, bitte erstellen Sie ein neues Zusatzfeld!',
  'The filter %filter% is unknown, please check the kitCommand!'
    => 'Der Filter %filter% ist nicht bekannt, bitte prüfen Sie das kitCommand!',
  'The form seems to be manipulated, abort action!'
    => 'Das Formular wurde vermutlich manipuliert, Aktion abgebrochen!',
  'The GUID was only valid for 24 hours and is expired, please contact the webmaster.'
    => 'Der Aktivierungslink war 24 Stunden gültig und ist abgelaufen, bitte nehmen Sie Kontakt mit dem Webmaster auf.',
  'The GUID was valid but can not get the contact record desired to the account!'
    => 'Die GUID war gültig, kann aber den Kontaktdatensatz nicht finden, der dem Benutzerkonto zugeordnet ist!',
  'The identifier %identifier% already exists!'
    => 'Der Bezeichner %identifier% existiert bereits!',
  'The import from KeepInTouch was successfull finished.'
    => 'Der Import aus KeepInTouch wurde erfolgreich abgeschlossen.',
  'The login <b>%login%</b> is already in use, please choose another one!'
    => 'Der Login <b>%login%</b> wird bereits verwendet, bitte legen Sie einen anderen Login fest!',
  'The login_name or a email address must be always set, can\'t insert the record!'
    => 'Das Feld <i>Anmeldename</i> oder eine <i>E-Mail Adresse</i> müssen immer gesetzt sein, kann den neuen Datensatz nicht einfügen!',
  'The name "%name%" contains illegal characters. Names should start with a letter, digit or underscore and only contain letters, digits, numbers, underscores ("_"), hyphens ("-") and colons (":").'
    => 'Der Bezeichner "%name%" enthält ungültige Zeichen. Bezeichner sollten mit einem Buchstaben; Zeichen oder Unterstrich beginnen und stets nur Buchstaben, Zahlen, Unterstriche ("_"), Trennzeichen ("-") und Doppelpunkte (":") enthalten.',
  'The phone number %number% exceeds the maximum length of %max% characters.'
    => 'Die Telefonnummer %number% überschreitet die maximal zulässige Länge von %max% Zeichen.',
  'The phone number %number% failed the validation, please check it!'
    => 'Die Telefonnummer %number% ist wahrscheinlich fehlerhaft, bitte überprüfen!',
  'The record with the ID %id% was successfull deleted.'
    => 'Der Datensatz mit der ID %id% wurde gelöscht.',
  'The record with the ID %id% was successfull inserted.'
    => 'Der Datensatz mit der ID %id% wurde erfolgreich eingefügt.',
  'The record with the ID %id% was successfull updated.'
    => 'Der Datensatz mit der ID %id% wurde erfolgreich aktualisiert.',
  'The submitted contact record will be proofed and published as soon as possible, we will send you an email!'
    => 'Der übermittelte Kontaktdatensatz wird so rasch wie möglich geprüft und veröffentlicht, wir melden uns bei Ihnen per E-Mail!',
  'The tag type %tag_name% already exists!'
    => 'Die Markierung %tag_name% existiert bereits und kann nicht erneut eingefügt werden!',
  'The title with the ID %title_id% does not exists!'
    => 'Die Anrede mit der ID %title_id% existiert nicht!',
  'The URL %url% is not valid, accepted is a pattern like http://example.com or https://www.example.com.'
    => 'Die URL %url% ist nicht gültig. Akzeptiert werden nur vollständige URL Angaben wie z.B. http://example.com oder https://www.example.com',
  'The value of the parameter contact_id must be an integer value and greater than 0'
    => 'Der Wert für den Parameter <i>contact_id</i> muss eine Ganzzahl größer als Null sein!',
  'The zip %zip% is not valid!'
    => 'Die Postleitzahl <b>%zip%</b> ist nicht gültig, bitte prüfen Sie Ihre Eingabe!',
  'The zip <strong>%zip%</strong> is not valid.'
    => 'Die Postleitzahl <strong>%zip%</strong> ist nicht gültig.',
  'There a no contacts to export.'
    => 'Es existieren keine Kontaktdatensätze, die exportiert werden könnten.',
  'There exists already a contact record for %login%, but this record is assigned to a <strong>%type%</strong> and can not be changed.'
    => 'Es existiert bereits ein Kontaktdatensatz für %login%, dieser ist jedoch einer <strong>%type%</strong> zugeordnet und kann nicht geändert werden.',
  'There exists already a contact record for you, but the status of this record is <strong>%status%</strong>. Please contact the webmaster to activate the existing record.'
    => 'Es existiert bereits ein Adressdatensatz für Sie, der Status dieses Datensatz ist jedoch auf <strong>%status%</strong> gesetzt. Bitte setzen Sie sich mit dem Webmaster in Verbindung um den Datensatz freizugeben.',
  'There exists already a contact record for you, but this record is assigned to a <strong>%type%</strong> and can not be changed. Please use the same type or contact the webmaster.'
    => 'Es existiert bereits ein Adressdatensatz für Sie, dieser ist jedoch einer <strong>%type%</strong> zugeordnet, der Typ kann nicht geändert werden. Bitte verwenden Sie den gleichen Kontakttyp oder kontaktieren Sie den Webmaster, damit dieser den Datensatz ändert.',
  'There exists no handling for the field type `%type%` neither as form nor as contact field!'
    => 'Es besteht kein Handlungsanweisung für das Feld vom Typ <em>%type%</em>, weder als Formular noch als Kontaktfeld!',
  'There exists no KeepInTouch installation at the parent CMS!'
    => 'Es existiert keine KeepInTouch Installation auf dem übergeordneten Content Management System!',
  'There where no contact records inserted or updated.'
    => 'Es wurden keine Kontaktdatensätze eingefügt oder aktualisiert.',
  'This is a sample panel text whith some unnecessary content'
    => 'Dies ist ein Beispiel für einen Panel Text mit etwas sinnfreiem Inhalt.',
  'This tag will be assigned to all user-defined `Contact` forms.'
    => 'Diese Markierung wird allen benutzerdefinierten `Contact` Formularen hinzugefügt.',
  'Title'
    => 'Titel',
  'Title definition'
    => 'Titel Definition',
  'Title id'
    => 'ID',
  'Title identifier'
    => 'Bezeichnung',
  'Title list'
    => 'Titel Übersicht',
  'Title long'
    => 'Anrede, lang',
  'Title short'
    => 'Anrede, kurz',
  'Titles'
    => 'Anreden',
  'To prevent a timeout of the script the import was aborted after import of %counter% records. Please reload this page to continue the import process.'
    => 'Das Script wurde nach dem Import von %counter% Datensätzen abgebrochen, um eine Überschreitung der zulässigen Ausführungsdauer zu vermeiden. Bitte laden Sie diese Seite erneut um den Import forzusetzen.',
  'Totally inserted %count% contact records'
    => 'Insgesamt wurden %count% Kontaktdatensätze eingefügt',
  'Totally updated %count% contact records'
    => 'Insgesamt wurden %count% Kontaktdatensätze aktualisiert',
  'Type'
    => 'Typ',
  'Unchecked'
    => '- ungeprüft -',
  'Unknown file format <strong>%format%</strong> to save the contact records.'
    => 'Unbekanntes Dateiformat <strong>%format%</strong> zu Sicherung der Kontaktdatensätze.',
  'Unknown phone number format <strong>%format%</strong>, please check the settings!'
    => 'Unbekannte Telefonnummer Formatierung <strong>%format%</strong>, bitte prüfen Sie die Einstellungen!',
  'Url'
    => 'URL',
  'Url id'
    => 'ID',
  'You are authenticated but not allowed to edit this contact'
    => 'Sie sind angemeldet, verfügen jedoch nicht über die Berechtigung diesen Kontaktdatensatz zu bearbeiten!',
  'You have assigned the field %field% twice! Please check the assignment!'
    => 'Sie haben das Feld <strong>%field%</strong> zweimal zugewiesen! Bitte prüfen Sie die Zuordnung!',
  'Your contact record is complete but not approved yet, please be patient.'
    => 'Ihr Kontaktdatensatz ist vollständig, wurde aber noch nicht geprüft und freigegeben, bitte haben Sie noch ein wenig Geduld.',
  'Your contact record is not complete, please check your address. You will not be able to publish anything at the portal as long as your contact record is locked.'
    => 'Ihr Kontaktdatensatz ist nicht vollständig, bitte prüfen Sie die Adressangaben. Sie können keine Informationen auf dem Portal veröffentlichen solange Ihr Kontaktdatensatz gesperrt ist.',
  'Your contact record is now published, we have send you a confirmation mail with further information.'
    => 'Ihr Kontaktdatensatz wurde veröffentlicht, wir haben Ihnen eine E-Mail mit weiteren Informationen zugesendet.',
  'Zip'
    => 'Postleitzahl',
  
);
