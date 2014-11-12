<?php

/**
 * kitFramework::Basic
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
  '- all files -'
    => '- alle Dateien -',
  '- please select -'
    => '- bitte auswählen -',
  'A brief summary of the translation status'
    => 'Eine Zusammenfassung des Übersetzungsstatus',
  'A <em>custom translation</em> will be saved in a separated file and not overwritten when the extension is updated.'
    => 'Eine <em>angepasste Übersetzung</em> wird in einer gesonderten Datei gespeichert und nicht überschrieben, wenn die Erweiterung aktualisiert wird.',
  'Abort'
    => 'Abbruch',
  'About'
    => 'Über ...',
  'About ...'
    => 'Über ...',
  'Access denied'
    => 'Zugriff verweigert',
  'Access to kitFramework User Accounts'
    => 'Zugriff auf die kitFramework Benutzerkonten',
  'Account'
    => 'Benutzerkonto',
  'Accounts'
    => 'Benutzerkonten',
  'Actual SMTP Settings'
    => 'Aktuelle SMTP Einstellungen',
  'Add the extension(s) <strong>%extension%</strong> to the catalog.'
    => 'Die Erweiterung(en) <strong>%extension%</strong> wurden dem Katalog hinzugefügt.',
  'Add the extension(s) <strong>%extension%</strong> to the register.'
    => 'Die Erweiterung(en) <strong>%extension%</strong> wurden dem Register hinzugefügt.',
  'Additional information'
    => 'Zusatzinformation',
  'Auto login'
    => 'Automatische Anmeldung',
  'Available extensions'
    => 'Verfügbare Erweiterungen',
  'Available release'
    => 'Verfügbare Ausgabe',
  'Available updates for your extensions'
    => 'Aktualisierungen die für Ihre Erweiterungen verfügbar sind',
  'Bad credentials'
    => 'Die Angaben sind unvollständig oder ungültig!',
  'Be aware: Changing the email address or username may influence kitFramework extensions which are using the account data to identify users.'
    => 'Bitte beachen Sie: Änderungen der E-Mail Adresse oder des Benutzernamen können sich auf kitFramework Anwendungen auswirken, die Daten der Benutzerkonten zur Identifizierung der Nutzer verwenden.',
  'Be aware: Changing your email address may influence kitFramework extensions which are using the account data to identify you - please contact the webmaster in these cases. The username can only be changed by the administrator.'
    => 'Bitte beachten Sie: Eine Änderung Ihrer E-Mail Adresse beeinflusst eventuell die mit Ihnen verbundenen Datensätze in den verschiedenen Anwendungen. Bitte informieren Sie in diesen Fällen den Webmaster. Der Benutzername kann nur durch einen Administrator geändert werden.',
  'Be aware: You are now authenticated as user <b>%username%</b>!'
    => 'Vorsicht! Sie sind momentan als der Benutzer <b>%username%</b> angemeldet!',
  'Can not read the extension.json for %name%!<br />Error message: %error%'
    => 'Kann die Beschreibungsdatei extension.json für die Erweiterung <b>%name%</b> nicht lesen!<br />Fehlermeldung: %error%',
  'Can not read the extension.json in %directory%!<br />Error message: %error%'
    => 'Kann die extension,json im Verzeichnis %directory% nicht lesen!<br />Fehlermeldung: %error%',
  'Can not read the file <strong>%file%</strong>!'
    => 'Kann die Datei <strong>%file%</strong> nicht lesen!',
  'Can not read the information file for the kitFramework!'
    => 'Kann die Beschreibungsdatei für das kitFramework nicht lesen!',
  'Can\'t create a new GUID as long the last GUID is not expired. You must wait 24 hours between the creation of new passwords.'
    => 'Es kann keine neue GUID erzeugt werden, solange die letzte noch gültig ist. Sie können das Anlegen eines neuen Passwort nur einmal innerhalb von 24 Stunden anfordern!',
  'Can\'t open the file <b>%file%</b>!'
    => 'Kann die Datei <b>%file%</b> nicht öffnen!',
  'Can\'t read the the %repository% from %organization% at Github!'
    => 'Kann das Repository %repository% von %organization% auf Github nicht lesen!',
  'Can\'t save the file %file%!'
    => 'Kann die Datei %file% nicht schreiben!',
  'Can\'t send the email to %email%!'
    => 'Konnte die E-Mail nicht an %email% übermitteln!',
  'Cancel'
    => 'Abbruch',
  'captcha-timeout'
    => 'Zeitüberschreitung bei der CAPTCHA Übermittlung, bitte versuchen Sie es erneut.',
  'Catalog'
    => 'Katalog',
  'Change'
    => 'Ändern',
  'Changed the CMS URL from %old_url% to %new_url%.'
    => 'Die CMS URL wurde von %old_url% zu %new_url% geändert.',
  'Changelog'
    => 'Änderungsprotokoll',
  'Check email settings'
    => 'E-Mail Einstellungen überprüfen',
  'Check MySQL settings'
    => 'MySQL Einstellungen überprüfen',
  'Check the CMS URL'
    => 'URL des Content Management System überprüfen',
  'Check the email settings and send a email to the webmaster for testing purpose'
    => 'E-Mail Einstellungen kontrollieren und eine Test E-Mail an den Webmaster schicken',
  'Cleanup the kitFramework cache directory.'
    => 'Das kitFramework Cache Verzeichnis wurde geleert.',
  'Click to sort column ascending'
    => 'Anklicken um die Spalte aufsteigend zu sortieren',
  'Click to sort column descending'
    => 'Anklicken um die Spalte absteigend zu sortieren',
  'Cms url'
    => 'CMS URL',
  'Cms url changed'
    => 'CMS URL',
  'commercial use only'
    => '- nur kommerzielle Verwendung -',
  'Configuration'
    => 'Konfiguration',
  'Configuration file'
    => 'Konfigurationsdatei',
  'Conflicting translations'
    => 'Kollidierende Übersetzungen',
  'Conflicts'
    => 'Konflikte',
  'Copied GUID to clipboard:'
    => 'GUID in die Zwischenablage kopiert:',
  'Copied kitCommand to clipboard!'
    => 'Das kitCommand wurde in die Zwischenablage kopiert!',
  'Copied the locale file to the source path: <i>%path%</i>'
    => 'Die Übersetzungsdatei wurde in das Quellverzeichnis <em>%path%</em> kopiert.',
  'Copy the GUID to the clipboard'
    => 'Die GUID in die Zwischenablage kopieren',
  'Copy this kitCommand to the clipboard'
    => 'Dieses kitCommand in die Zwischenablage kopieren',
  'Create a new .htaccess file for the kitFramework root directory.'
    => 'Es wurde eine neue .htaccess Datei für das kitFramework Wurzelverzeichnis angelegt.',
  'Create a new account'
    => 'Ein neues Benutzerkonto anlegen',
  'Create a new password'
    => 'Ein neues Password anlegen',
  'Create a unassigned translation'
    => 'Eine nicht zugeordnete Übersetzung erstellen',
  'Create CMS /config.bak and write new /config.php'
    => 'Sicherte die CMS Konfiguration in <var>/config.bak</var> und legte eine neue <var>/config.php</var> an.',
  'Create the physical directories needed by the flexContent permanent links.'
    => 'Legte die physikalischen Verzeichnisse an, die von den flexContent Permanent Links benötigt werden.',
  'Create the physical directories needed by the miniShop permanent links.'
    => 'Legte die physikalischen Verzeichnisse an die von den miniShop Permanent Links benötigt werden.',
  'Currently installed extensions'
    => 'Aktuell installierte Erweiterungen',
  'Custom'
    => 'Angepasst',
  'Custom translations'
    => 'Angepasste Übersetzungen',
  '<em>Custom translations</em> enable you to adapt the <em>regular</em> translations to your needs.'
    => '<em>Angepasste Übersetzungen</em> ermöglichen es Ihnen, die <em>regulären</em> Übersetzungen zu überschreiben und auf Ihre Anforderungen abzustimmen.',
  'Data replication'
    => 'Datenabgleich',
  'Db host'
    => 'DB Host',
  'Db name'
    => 'DB Name',
  'Db password'
    => 'DB Passwort',
  'Db port'
    => 'DB Port',
  'Db username'
    => 'DB Benutzername',
  'De'
    => 'Deutsch',
  'Delete account'
    => 'Benutzerkonto löschen',
  'Delete this account irrevocable'
    => 'Benutzerkonto unwiderruflich löschen',
  'Deleted the translation with the ID %id%.'
    => 'Die Übersetzung mit der ID <strong>%id%</strong> wurde gelöscht.',
  'Deleted widowed locale translation with the ID %id%.'
    => 'Die verwittwete Übersetzung mit der ID %id% gelöscht.',
  'Display name'
    => 'Anzeigename',
  'Displayname'
    => 'Angezeigter Name',
  'Documentation'
    => 'Dokumentation',
  'Download and prepare the <a href="%url%">kitFramework update</a>'
    => 'Die verfügbare <a href="%url%">kitFramework Aktualisierung</a> herunterladen und zur Installation vorbereiten.',
  'Dropped the tables for the i18nEditor.'
    => 'Die Tabellen für den i18nEditor wurden gelöscht.',
  'Duplicate translations'
    => 'Mehrfache Übersetzungen',
  'Duplicate translations: <strong>%duplicates%</strong><br />Conflicting translations: <strong>%conflicts%</strong><br />Unassigned translations: <strong>%unassigned%</strong>'
    => 'Mehrfache Übersetzungen: <strong>%duplicates%</strong><br />Kollidierende Übersetzungen: <strong>%conflicts%</strong><br />Nicht zugeordnete Übersetzungen: <strong>%unassigned%</strong>',
  'Duplicates'
    => 'Duplikate',
  'E-Mail'
    => 'E-Mail',
  'Edit'
    => 'Bearbeiten',
  'Edit a translations'
    => 'Übersetzung bearbeiten',
  'Edit kitFramework configuration file'
    => 'kitFramework Konfigurationsdatei bearbeiten',
  'Edit the locale %locale%'
    => 'Die %locale% Übersetzungen bearbeiten',
  'Edit translation'
    => 'Übersetzung bearbeiten',
  'Email'
    => 'E-Mail',
  'En'
    => 'Englisch',
  'Entry points'
    => 'Zugangspunkte',
  'Entry-points'
    => 'Zugangspunkte',
  'Error 404'
    => '404 - Datei nicht gefunden',
  'Error 405'
    => '405 - Route nicht gefunden',
  'Error 410'
    => '410 - Resource existiert nicht mehr',
  'Error 423'
    => '423 - Resource gesperrt',
  'Error creating image'
    => 'Error creating image',
  'Error executing the kitCommand <b>%command%</b>'
    => 'Bei der Ausführung des kitCommand <b>%command%</b> ist ein Fehler aufgetreten',
  'Error executing the kitFilter <b>%filter%</b>'
    => 'Fehler bei der Ausführung des kitFilter <strong>%filter%</strong>',
  '<b>Error</b>: Can\'t execute the kitCommand: <i>%command%</i>'
    => '<b>Fehler</b>: Das kitCommand <i>%command%</i> konnte nicht ausgeführt werden.',
  '<b>Error</b>: Can\'t execute the kitFilter: <i>%filter%</i>'
    => '<b>Fehler</b>: Konnte den kitFilter: <i>%filter%</i> nicht ausführen!',
  'Execute'
    => 'Ausführen',
  'Execute Update'
    => 'Aktualisierung durchführen',
  'Executed search run in %seconds% seconds.'
    => 'Der Suchlauf wurde in %seconds% Sekunden durchgeführt.',
  'Existing cms url'
    => 'CMS URL',
  'Existing db host'
    => 'DB Host',
  'Existing db name'
    => 'DB Name',
  'Existing db password'
    => 'DB Passwort',
  'Existing db port'
    => 'DB Port',
  'Existing db username'
    => 'DB Benutzername',
  'Existing table prefix'
    => 'Tabellen Prefix',
  'Explore the catalog for kitFramework extensions'
    => 'Durchsuchen Sie den Katalog mit kitFramework Erweiterungen',
  'Extension'
    => 'Erweiterung',
  'Extensions'
    => 'Erweiterungen',
  'Extensions catalog'
    => 'Erweiterungskatalog',
  'Failed to send a email with the subject <b>%subject%</b> to the addresses: <b>%failed%</b>.'
    => 'Eine E-Mail mit dem Betreff <b>%subject%</b> konnte an die folgenden Adressaten nicht übermittelt werden: <b>%failed%</b>.',
  'File'
    => 'Datei',
  'File path'
    => 'Dateipfad',
  'File path choice'
    => 'Dateipfad',
  'Filename'
    => 'Dateiname',
  'Files'
    => 'Dateien',
  'First steps'
    => 'Erste Schritte',
  'For the event with the ID %event_id% is no recurring defined.'
    => 'Für die Veranstaltung mit der ID %event_id% ist keine Wiederholung festgelegt.',
  'Forgot your password?'
    => 'Passwort vergessen?',
  'Framework uid'
    => 'Framework UID',
  'General alert container for kitCommands'
    => 'Allgemeine Benachrichtigung durch ein kitCommand',
  'General help container for kitCommand help files'
    => 'Allgemeine Hilfe für kitCommands',
  'Generate a globally unique identifier (GUID)'
    => 'Eine weltweit eindeutige Kennziffer erstellen (GUID)',
  'Get in touch with the developers, receive support, tipps and tricks for %command%!'
    => 'Treten Sie mit den Entwicklern in Kontakt und erhalten Unterstützung, erfahren Tipps sowie Tricks zu %command%!',
  'Get more information about %command%'
    => 'Erfahren Sie mehr über %command%',
  'Goodbye'
    => 'Auf Wiedersehen',
  'Hello %name%, you want to change your password, so please type in a new one, repeat it and submit the form. If you won\'t change your password just leave this dialog.'
    => 'Hallo %name%,<br />Sie möchten Ihr Passwort ändern, bitte geben Sie das neue Passwort ein, wiederholen Sie es zur Sicherheit und schicken Sie das Formular ab.<br />Falls Sie Ihr Passwort nicht ändern möchten, verlassen Sie bitte einfach diesen Dialog.',
  'Hello %name%,<br />you have asked to create a new password for the kitFramework hosted at %server%.'
    => 'Hallo %name%,<br />Sie haben darum gebeten ein neues Passwort für das kitFramework auf %server% zu erhalten.',
  'Help'
    => 'Hilfe',
  'help'
    => 'Hilfe',
  'help_accounts_list_json'
    => '<p>Diese Datei ermöglicht es Ihnen die angezeigten Spalten und die Sortierung der Felder in der <a href="%FRAMEWORK_URL%/admin/accounts/list" target="_blank">Übersicht der Benutzerkonten</a> zu ändern.</p><p>Verfügbare Felder für die Verwendung in <var>columns</var> und <var>list > order > by</var> sind: <var>id, username, email, password, displayname, last_login, roles, guid, guid_timestamp, guid_status, status</var> und <var>timestamp</var>. Mit <var>list > rows_per_page</var> legen Sie fest, wie viele Benutzerkonten pro Seite angezeigt werden.</p>',
  'help_cms_json'
    => '<p>Diese Konfigurationsdatei enthält Informationen zu dem übergeordneten Content Management System (CMS). Falls Sie die URL der Website verändert haben oder sich das Stammverzeichnis auf dem Webserver geändert hat, sollten Sie die Einstellungen in dieser Datei prüfen und anpassen.</p><p>Das <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#cmsjson" target="_blank">kitFramework WIKI</a> erläutert Ihnen alle Einstellungsmöglichkeiten für die <var>cms.json</var>.</p>',
  'help_config_jsoneditor_json'
    => '<p>Diese Konfigurationsdatei enthält die Einstellungen für den Konfigurations Editor, den Sie just in diesem Moment verwenden.</p><p>Normalerweise ist es nicht erforderlich an diesen Einstellungen etwas zu ändern. Die Datei enthält die Hilfeinformationen, die zu den einzelnen Konfigurationsdateien angezeigt werden sowie die Liste der verfügbaren Konfigurationsdateien um ein Überprüfen des Systems bei jedem Aufruf des Editors zu verhindern.</p><p>Falls Sie eine Konfigurationsdatei vermissen, z.B. für eine gerade erst installierte Erweiterung, verwenden Sie bitte den <key>Suchlauf</key> Schalter um ein erneutes Durchsuchen des Systems zu erzwingen.</p>',
  'help_doctrine_cms_json'
    => '<p>Diese Konfigurationsdatei enthält die Datenbankeinstellungen. Wenn Sie die Datenbankeinstellungen für das übergeordnete Content Management System (CMS) ändern, müssen Sie die Einstellungen in dieser Datei ebenfalls anpassen - andernfalls wird das kitFramework nicht mehr korrekt funktionieren.</p><p>Das <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#doctrinecmsjson" target="_blank">kitFramework WIKI</a> erläutert Ihnen alle Einstellungsmöglichkeiten für die <var>doctrine.cms.json</var>.</p>',
  'help_framework_json'
    => '<p>Dies ist die zentrale Konfigurationsdatei für das kitFramework. Hier können Sie den <var>DEBUG</var> und <var>CACHE</var> Modus ein- oder ausschalten und das kitFramework anweisen stets Ihre benutzerdefinierten Templates vor den Standardvorlagen zu laden.</p><p>Das <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration" target="_blank">kitFramework WIKI</a> erläutert Ihnen alle Einstellungsmöglichkeiten für die <var>framework.json</var>.</p>',
  'help_proxy_json'
    => '<p>Falls Sie einen Proxy Server verwenden benötigen Sie diese Konfigurationsdatei. Bitte fragen Sie Ihren Systemadministrator nach den benötigten Einstellungen.</p>',
  'help_swift_cms_json'
    => '<p>Diese Konfigurationsdatei wird benötigt um die E-Mail Einstellungen für das kitFramework festzulegen. Bitte fragen Sie Ihren E-Mail Anbieter nach den benötigten Einstellungen für den SMTP Server, Port, Benutzername und Passwort.</p><p>Sie können die E-Mail Einstellungen überprüfen, in dem Sie eine <a href="%FRAMEWORK_URL%/admin/test/mail" target="_blank">Testmail versenden</a>.</p><p>Das <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#swiftcmsjson" target="_blank">kitFramework WIKI</a> erläutert Ihnen alle Einstellungsmöglichkeiten für die <var>swift.cms.json</var>.</p>',
  'i18n'
    => 'i18n',
  'i18n Editor'
    => 'i18n Editor',
  'i18n truncate'
    => 'i18n Truncate',
  'Id'
    => 'ID',
  'If you have added a source to program file or a template execute a <em>search run</em> instead, the i18nEditor will find it.'
    => 'Falls Sie einer Programmdatei oder einem Template einen Quelltext hinzugefügt haben, sollten Sie stattdessen einen <em>Suchlauf</em> durchführen, der i18nEditor wird den Quelltext finden!',
  'If you have forgotten your password, you can order a link to create a new one. Please type in the email address assigned to your account and submit the form.'
    => 'Falls Sie Ihr Passwort vergessen haben, können Sie einen Link anfordern um ein neues Passwort zu erstellen. Bitte tragen Sie die E-Mail Adresse ein, die ihrem Konto zugeordnet ist und übermitteln Sie das Formular.',
  'If you have not asked to create a new password, just do nothing. The link above is valid only for 24 hours and your actual password has not changed now.'
    => 'Falls Sie kein neues Passwort angefordert haben, ignorieren Sie diese E-Mail bitte. Der o.a. Link ist lediglich für 24 Stunden gültig und ihr aktuelles Passwort wurde nicht geändert.',
  'incorrect-captcha-sol'
    => 'Der übermittelte CAPTCHA ist nicht korrekt.',
  'Info'
    => 'Info',
  'Information about the i18nEditor'
    => 'Informationen über den i18nEditor',
  'Information about the kitFramework'
    => 'Information über das kitFramework',
  'Install'
    => 'Installieren',
  'Install extension'
    => 'Erweiterung installieren',
  'Install, update or remove kitFramework Extensions'
    => 'Installieren, aktualisieren oder entfernen Sie kitFramework Erweiterungen',
  'Install, update or remove kitFramework extensions'
    => 'Installieren, Aktualisieren oder Entfernen von kitFramework Erweiterungen',
  'Installed'
    => 'Installiert',
  'installed'
    => 'installiert',
  'Installed extensions'
    => 'Installierte Erweiterungen',
  'Installed release'
    => 'Installierte Ausgabe',
  'Insufficient user role'
    => 'Ungenügende Zugangsberechtigung',
  'Internationalization'
    => 'Internationalisierung',
  'invalid-request-cookie'
    => 'Ungültige ReCaptcha Anfrage',
  'invalid-site-private-key'
    => 'Der private Schlüssel für den ReCaptcha Service ist ungültig, prüfen Sie die Einstellungen!',
  'Issues'
    => 'Mängel',
  'Json path'
    => 'JSON Pfad',
  'kitCommand - Alert'
    => 'kitCommand - Benachrichtigung',
  'kitCommand - Alert container'
    => 'kitCommand - Benachrichtigungscontainer',
  'kitCommand - General help container'
    => 'kitCommand - Allgemeine Hilfe',
  'kitFramework - Account'
    => 'kitFramework - Benutzerkonto',
  'kitFramework - Entry points'
    => 'kitFramework - Zugangspunkte',
  'kitFramework - First Login'
    => 'kitFramework - Erste Anmeldung',
  'kitFramework - Installed extensions'
    => 'kitFramework - Installierte Erweiterungen',
  'kitFramework - Link transmitted'
    => 'kitFramework - Link übermittelt',
  'kitFramework E-Mail Test'
    => 'kitFramework E-Mail Test',
  'kitFramework email test'
    => 'kitFramework E-Mail Test',
  'kitFramework Error'
    => 'kitFramework Fehler',
  'kitFramework password reset'
    => 'kitFramework Passwort zurücksetzen',
  'kitFramework User Account'
    => 'kitFramework Benutzerkonto',
  'Last registered file modification: <strong>%modification%</strong><br />Scanned files: <strong>%scanned%</strong><br />Locale hits: <strong>%hits%</strong>'
    => 'Letzte erfasste Dateiänderung: <strong>%modification%</strong><br />Durchsuchte Dateien: <strong>%scanned%</strong><br />Quelltext Treffer: <strong>%hits%</strong>',
  'License'
    => 'Lizenz',
  'Line'
    => 'Zeile',
  'Link transmitted'
    => 'Link übermittelt',
  'List'
    => 'Liste',
  'List of translation sources'
    => 'Liste der Quelltexte für die Übersetzungen',
  'Load file'
    => 'Datei laden',
  'Load the configuration file <strong>%file%</strong> into the editor.'
    => 'Die Konfigurationsdatei <strong>%file%</strong> wurde in den Editor geladen.',
  'Load the selected configuration file into the editor'
    => 'Die ausgewählte Datei in den Editor laden',
  'Locale'
    => 'Sprache',
  'Locale file'
    => 'Übersetzungsdatei',
  'Locale id'
    => 'Locale ID',
  'Locale locale'
    => 'Sprache',
  'Locale remark'
    => 'Notiz',
  'Locale source'
    => 'Quelltext',
  'Locale type'
    => 'Typ',
  'Locale: <strong>%locale%</strong><br />Sources: <strong>%total%</strong><br />Translations: <strong>%translated%</strong><br />Pending: <strong>%pending%</strong>'
    => 'Sprache: <strong>%locale%</strong><br />Quelltexte: <strong>%total%</strong><br />Übersetzungen: <strong>%translated%</strong><br />Anstehend: <strong>%pending%</strong>',
  'Locales waiting for a translation'
    => 'Lokalisierungen die auf eine Übersetzung warten',
  'Login'
    => 'Anmelden',
  'Login - kitFramework'
    => 'Anmeldung - kitFramework',
  'Logout'
    => 'Abmelden',
  'Message'
    => 'Mitteilung',
  'Migration result'
    => 'Migrations Ergebnis',
  'Missing the parameter "expression"!'
    => 'Vermisse den Parameter <em>expression</em>!',
  'Missing the parameter: %parameter%'
    => 'Benötige den Parameter: %parameter%',
  'Missing the user ID!'
    => 'Vermisse die Anwender ID!',
  'Modified'
    => 'Geändert',
  'More information ...'
    => 'Weitere Informationen ...',
  'Mysql changed'
    => 'MySQL wurde geändert',
  'Name'
    => 'Bezeichner',
  'Need help? Please visit the <a href="%url%" target="_blank">phpManufaktur Support Group</a>.'
    => 'Benötigen Sie Hilfe? Bitte besuchen Sie die <a href="%url% target="_blank">phpManufaktur Support Group</a>.',
  'New kitFramework release available!'
    => 'Es ist eine neue kitFramework Release verfügbar!',
  'No'
    => 'Nein',
  'No account? <a href="%register_url%">Register a user account for DogPaw</a>!'
    => 'Kein Benutzerkonto? <a href="%register_url%">Melden Sie sich kostenlos an</a>!',
  'No fitting user role dectected!'
    => 'Es wurde kein passendes Benutzerrecht gefunden',
  'No sources available, please <a href="%url%">start a search run</a>!'
    => 'Es sind keine Quelltexte verfügbar, bitte <a href="%url%">führen Sie einen Suchlauf durch</a>!',
  'One or more translation for this source is conflicting!'
    => 'Eine oder mehrere Übersetzungen für diesen Quelltext kollidieren und sind widersprüchlich.',
  'Oooops, missing the alert which should be prompted here ... '
    => 'Hoppla, da fehlt die Meldung die hier eigentlich angezeigt werden sollte ...',
  'Ooops, don\'t know how to handle the locale source \'%source%\', please check the protocol.'
    => 'Weiß nicht, wie ich die Quelle \'%source%\' handhaben soll, bitte prüfen Sie das Protokoll!',
  'Open the %extension% extension in kitFramework'
    => 'Die Erweiterung %extension% im kitFramework öffnen',
  'Open the CMS Tool in kitFramework'
    => 'Das CMS Tool direkt im kitFramework öffnen',
  'Open this helpfile in a new window'
    => 'Diese Hilfedatei in einem neuen Fenster öffnen',
  'Overview'
    => 'Übersicht',
  'Parse the kitFramework for locale strings, add custom translations and administrate the internationalization'
    => 'Das kitFramework nach Lokalisierungen durchsuchen, benutzerdefinierte Übersetzungen hinzufügen und die Internationalisierungen verwalten.',
  'Password'
    => 'Passwort',
  'Password changed'
    => 'Passwort geändert',
  'Password repeat'
    => 'Passwort wiederholen',
  'Pending translations'
    => 'Anstehende Übersetzungen',
  'Please authenticate'
    => 'Bitte authentifizieren Sie sich',
  'Please be aware that <em>translations</em> for the locale <strong>EN</strong> (english) are more often than not identical with the <em>source</em> - for this reason they will be only added to a language file if the <em>translation</em> differ from <em>source</em>.'
    => 'Bitte beachten Sie, dass <em>Übersetzungen</em> für die Sprache <strong>EN</strong> (Englisch) häufig mit den <em>Quelltexten</em> identisch sind - aus diesem Grund werden der Sprachdatei in diesem Fall nur Übersetzungen hinzugefügt, wenn die <em>Übersetzung</em> vom <em>Quelltext</em> abweicht.',
  'Please check the username and password and try again!'
    => 'Bitte prüfen Sie den angegebenen Benutzernamen sowie das Passwort und versuchen Sie es erneut!',
  'Please <a href="%link%" target="_blank">comment this help</a> to improve the kitCommand <b>%command%</b>.'
    => 'Bitte <a href="%link%" target="_blank">kommentieren und ergänzen Sie diese Hilfe</a> um das kitCommand <b>%command%</b> zu verbessern.',
  'Please execute the available updates.'
    => 'Bitte führen Sie die verfügbaren Aktualisierungen durch!',
  'Please login to the kitFramework with your username or email address and the assigned password. Your can also use your username and password for the CMS.'
    => 'Bitte melden Sie sich am kitFramework mit Ihrem Benutzernamen oder Ihrer E-Mail Adresse und Ihrem Passwort an. Sie können sich auch mit Ihrem Benutzernamen und Passwort für das CMS anmelden.',
  'Please report all issues and help to improve %command%!'
    => 'Bitte melden Sie alle auftretenden Probleme und helfen Sie mit %command% zu verbessern!',
  'Please select a specific locale file or select <em>- all files -</em> to see all available translations.'
    => 'Bitte wählen Sie die gewünschte Übersetzungsdatei oder wählen Sie <em>- alle Dateien -</em> um die jeweils verfügbaren Übersetzungen zu sehen.',
  'Please select the configuration file you want to edit.'
    => 'Bitte wählen Sie die Konfigurationsdatei aus, die Sie bearbeiten möchten.',
  'Please use the following link to create a new password: %reset_url%'
    => 'Bitte verwenden Sie den folgenden Link um ein neues Passwort anzulegen:<br />%reset_url%',
  'Please <a href="%source%">visit this page</a> to view the content of this iframe.'
    => 'Bitte <a href="%source%">besuchen Sie diese Seite</a> um den Inhalt dieses iFrame zu sehen.',
  'Problems'
    => 'Probleme',
  'Problems with the translation data'
    => 'Probleme mit den Übersetzungsdaten',
  'published at'
    => 'veröffentlicht am',
  'Real active user roles'
    => 'Tatsächlich aktive Anwenderrechte',
  'Redirect'
    => 'Weiterleitung',
  'Referenced files'
    => 'Verweisende Dateien',
  'References'
    => 'Verweise',
  'Regards, Your kitFramework team'
    => 'Mit freundlichen Grüßen<br />Ihr kitFramework Team',
  'Release'
    => 'Release',
  'Remark'
    => 'Anmerkung',
  'Remove'
    => 'Entfernen',
  'removed'
    => 'entfernt',
  'Repeat by pattern, i.e. at the last tuesday of the month'
    => 'Nach einem Muster, z.B. am letzten Donnerstag im Monat',
  'Repeat Password'
    => 'Passwort wiederholen',
  'Repeat sequently at day x of month'
    => 'Regelmäßig am Tag x des Monats',
  'Report problems'
    => 'Fehler melden',
  'Rescan'
    => 'Suchlauf',
  'Reveal this e-mail address'
    => 'Anklicken um die vollständige E-Mail Adresse anzuzeigen',
  'Roles'
    => 'Benutzerrechte, Rollen',
  'Role admin'
    => '(Voll-)Administrator',
  'Role contact admin'
    => 'Kontakt Administrator',
  'Role contact edit'
    => 'Kontakte bearbeiten',
  'Role contact edit own'
    => 'Eigene Kontakte bearbeiten',
  'Role flexcontent admin'
    => 'flexContent Administrator',
  'Role flexcontent editor'
    => 'flexContent Editor',
  'Role mediabrowser admin'
    => 'Mediabrowser Administrator',
  'Role mediabrowser user'
    => 'Mediabrowser Benutzer',
  'Role minishop admin'
    => 'miniShop Administrator',
  'Role user'
    => 'Benutzer',
  'Save file'
    => 'Datei sichern',
  'Scan for installed extensions'
    => 'Nach installierten Erweiterungen suchen',
  'Scan the kitFramework for existing configuration files'
    => 'Das kitFramework nach Konfigurationsdateien durchsuchen',
  'Scan the online catalog for available extensions'
    => 'Den online Katalog nach verfügbaren Erweiterungen durchsuchen',
  'Secured area'
    => 'Geschützter Bereich',
  'Select'
    => 'Auswählen',
  'Select file'
    => 'Datei auswählen',
  'Send a account information to the user %name%'
    => 'Dem Benutzer %name% wurde eine Kontoinformation zugesendet.',
  'Send a email with the FRAMEWORK_UID'
    => 'E-Mail mit der FRAMEWORK_UID versenden',
  'Send account info to the user'
    => 'Dem Benutzer eine Kontoinformation zusenden',
  'Send email'
    => 'E-Mail senden',
  'Send email (only if the password has changed)'
    => 'Zugangsdaten senden (nur wenn das Passwort geändert wurde)',
  'Server email'
    => 'Server E-Mail',
  'Server name'
    => 'Server Name',
  'Show a list of all installed kitCommands'
    => 'Eine Liste mit den installierten kitCommands anzeigen',
  'Show the given kitCommand expression but dont execute it'
    => 'Den übergebenen kitCommand Ausdruck anzeigen aber nicht ausführen.',
  'Simulate the given kitCommand expression'
    => 'Den übergebenen kitCommand Ausdruck simulieren',
  'Smtp auth mode'
    => 'SMTP Authentifizierung',
  'Smtp encryption'
    => 'SMTP Verschlüsselung',
  'Smtp host'
    => 'SMTP Host',
  'Smtp password'
    => 'SMTP Passwort',
  'Smtp port'
    => 'SMTP Port',
  'Smtp username'
    => 'SMTP Benutzername',
  'Sorry, but only Administrators are allowed to access this kitFramework extension.'
    => 'Ihre Berechtigung ist nicht ausreichend, nur Administratoren dürfen das kitFramework CMS Tool verwenden.',
  'Sorry, but the configuration file <strong>%filename%</strong> was not found. Please be aware that this controller may fail if you try to open a configuration file of a just installed extension, perhaps the extension must be executed first and you should also do a <key>rescan</key> for the configuration files.'
    => 'Entschuldigung, die Konfigurationsdatei <strong>%filename%</strong> wurde nicht gefunden. Bitte beachten Sie, dass der Controller die Datei möglicherweise nicht findet, wenn Sie versuchen eine Konfigurationsdatei einer gerade erst installierten Erweiterung zu öffnen, eventuell muss die Erweiterung mindestens einmal ausgeführt worden sein und Sie sollten einen <key>Suchlauf</key> durchführen.',
  'Sorry, but the submitted GUID is invalid. Please contact the webmaster.'
    => 'Die übermittelte GUID ist ungültig. Bitte nehmen Sie mit dem Webmaster Kontakt auf.',
  'Sorry, but you are not allowed to access any entry point!'
    => 'Sie sind leider nicht berechtigt auf einen der kitFramework Zugangspunkte zuzugreifen.',
  'Sorry, there is currently no information available about <strong>%file%</strong>, please suggest a hint and help to improve the Configuration Editor!'
    => 'Entschuldigung, es ist leider keine Information zu <strong>%file%</strong> verfügbar, bitte schlagen Sie einen Hinweis vor und helfen Sie mit den Konfigurations Editor zu verbessern!',
  'Source'
    => 'Quelltext',
  'Sources'
    => 'Quelltexte',
  'Start search run'
    => 'Suchlauf starten',
  'Status'
    => 'Status',
  'Submit'
    => 'Übermitteln',
  'Successful created the tables for the i18nEditor.'
    => 'Tabellen für den i18nEditor erfolgreich angelegt.',
  'Successful inserted a new unassigned translation.'
    => 'Die nicht zugeordnete Übersetzung wurde erfolgreich eingefügt.',
  'Successful moved the translation to the extension %extension%.'
    => 'Die Übersetzung wurde erfolgreich in die Erweiterung %extension% verschoben.',
  'Successful scanned the kitFramework for *.json configuration files'
    => 'Das kitFramework wurde nach *.json Konfigurationsdateien durchsucht',
  'Successfull %mode% the extension %extension%.'
    => 'Die Erweiterung %extension% wurde erfolgreich %mode%.',
  'Successfull created a account for the user %name%.'
    => 'Für den Benutzer %name% wurde ein Konto eingerichtet.',
  'Successfully installed the extension %extension%.'
    => 'Die Erweiterung %extension% wurde erfolgreich installiert.',
  'Successfull put the locale entries to the locale file.'
    => 'Die Übersetzungen wurden erfolgreich in die Übersetzungsdatei geschrieben.',
  'Successfull scanned the kitFramework for installed extensions.'
    => 'Das kitFramework wurde nach installierten Erweiterungen durchsucht.',
  'Successfull scanned the kitFramework online catalog for available extensions.'
    => 'Der online Katalog für das kitFramework wurde nach verfügbaren Erweiterungen durchsucht.',
  'Successfull uninstalled the extension %extension%.'
    => 'Die Erweiterung %extension% wurde erfolgreich entfernt.',
  'Successfull updated the extension %extension%.'
    => 'Die Erweiterung %extension% wurde erfolgreich aktualisiert.',
  'Support'
    => 'Unterstützung',
  'Switch back to the administration of this user account'
    => 'Zur Verwaltung dieses Benutzerkontos zurückkehren',
  'Switch to developer mode to get also information about problems and conflicts.'
    => 'Wechseln Sie in den <em>Entwickler Modus</em>, um zusätzlich Informationen über Probleme und Konflikte zu erhalten.',
  'Switch to the kitFramework Entry-points'
    => 'Zu den kitFramework Zugangspunkten wechseln',
  'Switch to this user to see the real active roles'
    => 'Zu diesem Anwender umschalten um die aktiven Rechte zu sehen',
  'Table prefix'
    => 'Taellen Prefix',
  'Template'
    => 'Vorlage',
  'Test email'
    => 'Test E-Mail',
  'Thank you for using the kitFramework'
    => 'Vielen Dank für den Einsatz des kitFramework',
  'The account for the user %name% was successfull deleted.'
    => 'Das Benutzerkonto für %name% wurde gelöscht.',
  'The account was not changed.'
    => 'Das Benutzerkonto wurde nicht verändert.',
  'The account was succesfull updated.'
    => 'Das Benutzerkontor wurde aktualisiert',
  'The account with the ID %id% does not exists!'
    => 'Das Benutzerkonto mit der ID %id% existiert nicht!',
  'The account with the username or email address %name% does not exists!'
    => 'Es existiert kein Benutzerkonto für den Benutzername oder die E-Mail Adresse %name%!',
  'The both passwords you have typed in does not match, please try again!'
    => 'Die beiden Passwörter die Sie eingegeben haben stimmen nicht überein, bitte versuchen Sie es noch einmal!',
  'The configuration file <strong>%file%</strong> was successful saved.'
    => 'Die Konfigurationsdatei <strong>%file%</strong> wurde erfolgreich gesichert.',
  'The controller has detected <strong>%count%</strong> configuration files with the name <strong>%filename%</strong> and loaded the first hit into the editor.'
    => 'Der Controller hat <strong>%count%</strong> Konfigurationsdateien mit der Bezeichnung <strong>%filename%</strong> gefunden und den ersten Treffer in den Editor geladen.',
  'The displayname %displayname% is already in use by another user, please select another one!'
    => 'Der Anzeigename <em>%displayname%</em> wird bereits von einem anderen Anwender verwendet, bitte wählen Sie eine alternative Bezeichnung!',
  'The email address %email% is already used by another account!'
    => 'Die E-Mail Adresse %email% wird bereits von einem anderen Benutzerkonto verwendet!',
  'The email address %email% is invalid!'
    => 'Die E-Mail Adresse %email% ist ungültig, bitte prüfen Sie Ihre Eingabe!',
  'The extension %extension% does not exists.'
    => 'Die Erweiterung %extension% existiert nicht.',
  'The extension %extension% was successful removed.'
    => 'Die Erweiterung %extension% wurde erfolgreich entfernt.',
  'The extension with the ID %extension_id% does not exists!'
    => 'Die Erweiterung mit der ID %extension_id% existiert nicht!',
  'The extension.json of <b>%name%</b> does not contain all definitions, check GUID, Group and Release!'
    => 'Die Beschreibungsdatei extension.json für die Erweiterung <b>%name%</b> enthält nicht alle Definitionen, prüfen Sie <i>GUID</i>, <i>Group</i> und <i>Release</i>!',
  'The file %file% does not exists in Gist %gist_id%!'
    => 'Die Datei %file% existiert nicht im Gist %gist_id%',
  'The file <strong>%file%</strong> does not exists!'
    => 'Die Datei <strong>%file%</strong> existiert nicht!',
  'The file <i>%file%</i> does not exists!'
    => 'Die Datei <em>%file%</em> existiert nicht!',
  'The file <strong>%file%</strong> is not readable!'
    => 'Die Datei <strong>%file%</strong> ist nicht lesbar!',
  'The form is not valid, please check your input and try again!'
    => 'Das Formular ist nicht gültig, bitte überprüfen Sie Ihre Eingabe und übermitteln Sie das Formular erneut!',
  'The form seems to be compromitted, can not check the data!'
    => 'Das Formular scheint kompromitiert worden zu sein, kann die Daten nicht ändern!',
  'The <var>FRAMEWORK_UID</var> you typed in was invalid.'
    => 'Die <var>FRAMEWORK_UID</var> die Sie angegeben haben ist ungültig.',
  '<p>The i18nEditor has not detected the following sources in any kitFramework file.</p><p>Maybe the source is really not used anywhere, but it is also possible that a source is used in a file and the i18nEditor Parser is not able to assign the source to a translation.</p><p>Search for the sources in the extension files and check the context. This list will be refreshed at the next search run.</p>'
    => '<p>Der i18nEditor konnte die folgenden Quelltexte in keiner kitFramework Datei finden.</p><p>Möglicherweise werden die Quelltexte tatsächlich nicht verwendet und können entfernt werden. Es ist jedoch genauso möglich, dass die Quelltexte in den Dateien verwendet werden und der i18nEditor Parser nicht in der Lage dazu ist die Zuordnung zu erkennen.</p><p>Suchen Sie nach den Quelltexten in den Dateien und prüfen Sie den jeweiligen Kontext. Diese Liste wird bei jedem Suchlauf neu erstellt.</p>',
  'The kitFramework has successfull updated. Because this update has changed elementary functions and methods of the kitFramework core you should check the behaviour of all kitFramework applications in backend and frontend of your website within the next days. There exists a copy of your previous kitFramework core files, so it is possible to roll back if needed.'
    => '<p>Das kitFramework wurde erfolgreich aktualisiert. Da diese Aktualisierung wesentliche Funktionen und Methoden des kitFramework Kern geändert hat, sollten Sie das Verhalten aller kitFramework Anwendungen in den nächsten Tagen beobachten - bitte melden Sie alle Störungen an das Support Team.</p><p>Es existiert eine Kopie der vorherigen kitFramework Kern Dateien, im Notfall ist es möglich zu der vorherigen kitFramework Version zurückzukehren.',
  'The kitFramework restore directory was successful removed'
    => 'Das kitFramework Wiederherstellungsverzeichnis wurde erfolgreich entfernt.',
  'The kitFramework update is prepared, now you can <a href="%url%">remove the existing one and install the new kitFramework release</a>.'
    => 'Die kitFramework Aktualisierung ist vorbereitet. Sie können jetzt <a href="%url%">die vorhandenen kitFramework Kern Dateien entfernen und die neue kitFramework Version installieren</a>.',
  'The kitFramework was never checked for the existing locale sources and translations, please start a search run!'
    => 'Das kitFramework wurde noch nicht nach Quelltexten für Übersetzungen durchsucht, bitte starten Sie einen Suchlauf!',
  'The parameter <code>%parameter%[%value%]</code> for the kitCommand <code>~~ %command% ~~</code> is unknown, please check the parameter and the given value!'
    => 'Der Parameter <code>%parameter%[%value%]</code> für das kitCommand <code>~~ %command% ~~</code> ist nicht bekannt oder übergibt einen ungültigen Wert, bitte prüfen Sie Ihre Eingabe!',
  'The password for the kitFramework was successfull changed. You can now <a href="%login%">login using the new password</a>.'
    => 'Ihr Passwort für das kitFramework wurde erfolgreich geändert.<br />Sie können sich jetzt <a href="%login%">mit Ihrem neuen Passwort anmelden</a>.',
  'The password you have typed in is not strength enough. Please choose a password at minimun 8 characters long, containing lower and uppercase characters, numbers and special chars. Spaces are not allowed.'
    => 'Das übermittelte Passwort ist nicht stark genug. Bitte wählen Sie ein Passwort mit mindestens 8 Zeichen Länge, mit einem Mix aus Groß- und Kleinbuchstaben, Zahlen und Sonderzeichen. Leerzeichen sind nicht gestattet.',
  'The password you typed in is not correct, please try again.'
    => 'Das angegebene Passwort is nicht korrekt, bitte geben Sie es erneut ein',
  'The received extension.json does not specifiy the path of the extension!'
    => 'Die empfangene extension.json enthält nicht den Installationspfand für die Extension!',
  'The received repository has an unexpected directory structure!'
    => 'Das empfangene Repository hat eine unterwartete Verzeichnisstruktur und kann nicht eingelesen werden.',
  'The record was successfull inserted'
    => 'Der Datensatz wurde erfolgreich eingefügt',
  'The record was successfull updated'
    => 'Der Datensatz wurde erfolgreich aktualisiert!',
  'The record with the ID %id% does not exists!'
    => 'Es existiert kein Datensatz mit der ID %id%!',
  'The record with the ID %id% was successfull updated.'
    => 'Der Datensatz mit der ID %id% wurde erfolgreich aktualisiert.',
  'The requested page could not be found!'
    => 'Die angeforderte Seite wurde nicht gefunden!',
  'The requested page has been removed and is no longer available!'
    => 'Die angeforderte Seite wurde entfernt und ist nicht mehr verfügbar!',
  'The requested page is locked and temporary not available!'
    => 'Die angeforderte Seite ist gesperrt und vorübergehend nicht verfügbar!',
  'The requested route does not exists!'
    => 'Die angeforderte Route existiert nicht.',
  'The ROLE_USER is needed if you want enable the user to access and change his own account. The ROLE_ADMIN is the highest available role and grant access to really everything.'
    => 'Das Recht ROLE_USER ist erforderlich um einem Benutzer Zugriff auf sein Konto zu ermöglichen. Das Recht ROLE_ADMIN ist das höchste verfügbare Recht und garantiert einen uneingeschränkten Zugriff auf alle Funktionen des kitFramework.',
  'The source <strong>%source%</strong> exists already as unassigned translation record, can not insert it!'
    => 'Der Quelltext <strong>%source%</strong> existiert bereits als <em>nicht zugeordnete</em> Übersetzung, kann den Datensatz nicht hinzufügen!',
  'The source <a href="%url%">%source%</a> is already in use, can not insert it as unassigned translation!'
    => 'Der Quelltext <a href="%url%">%source%</a> wird bereits verwendet, kann ihn nicht als <em>nicht zugeordenete</em> Übersetzung einfügen!',
  'The status of this translation is set to <strong>CONFLICT</strong>. This problem must be solved by a developer.'
    => 'Der Status dieser Übersetzung is auf <strong>CONFLICT</strong> gesetzt. Dieses Problem muss durch einen Entwickler gelöst werden.',
  'The submitted GUID is expired and no longer valid.<br />Please <a href="%password_forgotten%">order a new link</a>.'
    => 'Die übermittelte GUID ist abgelaufen und nicht länger gültig.<br />Bitte <a href="%password_forgotten%">fordern Sie einen neuen Link an</a>.',
  'The submitted GUID was already used and is no longer valid.<br />Please <a href="%password_forgotten%">order a new link</a>.'
    => 'Die übermittelte GUID wurde bereits verwendet und ist nicht mehr gültig.<br />Bitte <a href="%password_forgotten%">fordern Sie einen neuen Link an</a>.',
  'The test mail to %email% was successfull send.'
    => 'Die Test E-Mail wurde erfolgreich an %email% versendet!',
  'The translation conflict for the locale source <strong>%source%</strong> has been solved!'
    => 'Der Übersetzungskonflikt für den Quelltext <strong>%source%</strong> ist aufgelöst!',
  'The translation has not changed.'
    => 'Die Übersetzung wurde nicht geändert.',
  'The URL <strong>%url%</strong> is not valid, please check your input!'
    => 'Die URL <strong>%url%</strong> is nicht gültig, bitte prüfen Sie Ihre Eingabe!',
  'The username %username% is already in use, please select another one!'
    => 'Der Benutzername %username% wird bereits verwendet, bitte wählen Sie einen anderen Benutzernamen.',
  'There a no settings changed, nothing to do ...'
    => 'Es wurden keine Einstellungen geändert, es gibt nichts zu tun ...',
  'There a no translated sources available'
    => 'Es sind keine übersetzten Quelltexte verfügbar.',
  'There are new catalog information available, <strong><a href="%route%">please update the catalog</a></strong>.'
    => 'Es sind neue Katalog Informationen verfügbar, <strong><a href="%route%">bitte aktualisieren Sie den Katalog</a></strong>.',
  'There are no roles assigned to this user.'
    => 'Diesem Benutzer sind keine Rechte zugewiesen',
  'There are updates available, <strong><a href="%route%">please check out your installed extensions</a></strong>!'
    => 'Es sind Aktualisierungen verfügbar, <strong><a href="%route%">bitte überprüfen Sie die installierten Erweiterungen</a></strong>!',
  'There exist an duplicate locale key for <strong>%key%</strong> in the file <em>%file%</em>!'
    => 'Es existiert ein doppelter Schlüssel für <strong>%key%</strong> in der Datei <em>%file%</em>!',
  'There exists a kitFramework restore directory, if your system is working fine you can <a href="%route%">remove this directory</a>.'
    => 'Es existiert ein kitFramework Wiederherstellungsverzeichnis. Wenn Ihr System einwandfrei mit der neuen kitFramework Version funktioniert können Sie <a href="%route%">dieses Verzeichnis entfernen</a>.',
  'There exists no catalog entry for the extension %name% with the GUID %guid%.'
    => 'Es existiert kein Katalog Eintrag für die Erweiterung %name% mit der GUID %guid%.',
  'There exists no conflicts.'
    => 'Es bestehen keine Konflikte.',
  'There exists no custom translations for this installation!'
    => 'Es existieren keine angepassten Übersetzungen für diese kitFramework Installation!',
  'There exists no duplicate translations.'
    => 'Es existieren keine doppelten Übersetzungen.',
  'There exists no kitFramework restore directory!'
    => 'Es existiert kein kitFramework Wiederherstellungsverzeichnis!',
  'There exists no pending translations for the locale %locale%.'
    => 'Es existieren keine <em>anstehenden</em> Übersetzungen für die Sprache %locale%.',
  'There exists no references for the locale source with the id %locale_id%.'
    => 'Es existieren keine Verweise für die lokale Quelle mit der ID %locale_id%.',
  'There exists no statistic information about the locale <strong>%locale%</strong>, please execute a <em>search run</em>!'
    => 'Es existieren keine statistischen Informationen über die Sprache <strong>%locale%</strong>, bitte führen Sie einen <em>Suchlauf</em> durch!',
  'There exists no translations for the locale source with the id %locale_id%'
    => 'Es existieren keine Übersetzungen für den Quelltext mit der ID %locale_id%.',
  'There exists no unassigned translations.'
    => 'Es existieren keine <em>nicht zugeordneten</em> Übersetzungen.',
  'There exists no user with the submitted email address.'
    => 'Die übermittelte E-Mail Adresse kann keinem Benutzer zugeordnet werden.',
  'There is a <a href="%route%">new kitFramework release available</a>!'
    => 'Es ist eine <a href="%route%">kitFramework Aktualisierung für die Kerndateien verfügbar</a>!',
  'There is no help available for the kitCommand <b>%command%</b>.'
    => 'Für das kitCommand <b>%command%</b> ist keine Hilfe verfügbar.',
  'This link enable you to change your password once within 24 hours.'
    => 'Dieser Link ermöglicht es Ihnen, ihr Passwort einmal innerhalb von 24 Stunden zu ändern.',
  '<p>This list contain %count% translation sources.</p><p>Click at <em>ID</em> or at <em>References</em> for information in which files the sources are used.</p>'
    => '<p>Diese Liste enthält %count% Quelltexte als Basis für die Übersetzungen.</p><p>Klicken Sie auf <em>ID</em> oder <em>Verweise</em> um zu erfahren in welchen Dateien diese Quelltexte verwendet werden.</p>',
  '<p>This list show you conflicting translations, which mean: the source is translated more then once and in a different way.</p><p>Remove conflicting translations or change the source if you need different translations.</p><p>Start a search run if you have solved a conflict to remove the entry from the list.</p>'
    => '<p>Diese Liste zeigt Ihnen kollidierend Übersetzungen: der jeweilige Quelltext wurde mehr als einmal und auf unterschiedliche Weise übersetzt.</p><p>Entfernen sie kollidierende Übersetzungen oder ändern Sie den Quelltext, falls Sie unterschiedliche Übersetzungen benötigen.</p><p>Starten Sie einen Suchlauf, wenn Sie einen Konflikt gelöst haben.</p>',
  '<p>This list shows duplicate translations.</p><p>Maybe it is not possible to avoid all duplicate translations, for example if the same translation is used by different extensions and they can be used independent. Nevertheless you should check these entries, perhaps you can avoid the one or other.</p>'
    => '<p>Diese Liste zeigt Ihnen mehrfache Übersetzungen an.</p><p>Es ist wahrscheinlich nicht möglich jede mehrfache Übersetzung zu vermeiden, z.B. weil eine Übersetzung von unterschiedlichen Erweiterungen benötigt wird und diese nicht unbedingt gemeinsam installiert werden müssen. Gleichwohl sollten Sie die Einträge überprüfen, vielleicht lässt sich ja das eine oder andere Duplikat vermeiden.</p>',
  'This locale file does not contain any translations!'
    => 'Diese Sprachdatei enthält keine Übersetzungen!',
  'This sources are currently not translated to <em>%locale%</em>, they are <em>pending</em>.'
    => 'Diese Quelltexte wurden noch nicht in <em>%locale%</em> übersetzt und sind als <em>anstehend</em> gekennzeichnet.',
  'This user are assigned %count% roles.'
    => 'Diesem Benutzer sind insgesamt <b>%count%</b> Rechte zugewiesen.',
  'This value is not a valid email address.'
    => 'Es wurde keine gültige E-Mail Adresse übergeben!',
  'Too many invalid inputs - you are locked for this session!'
    => 'Zu viele ungültige Eingaben - Sie sind für diese Sitzung gesperrt!',
  'Translated'
    => 'Übersetzt',
  'Translation'
    => 'Übersetzung',
  'Translation custom file'
    => 'Übersetzung anpassen',
  'Translation delete checkbox'
    => 'diese Übersetzung löschen',
  'Translation id'
    => 'ID',
  'Translation move to'
    => 'Übersetzung verschieben',
  'Translation remark'
    => 'Bemerkung',
  'Translation status'
    => 'Status',
  'Translation text'
    => 'Übersetzung',
  'Translations'
    => 'Übersetzungen',
  'Translations which are not assigned to any files'
    => 'Übersetzungen, die keiner Datei zugeordnet sind',
  'Translations which causes a conflict'
    => 'Übersetzungen die einen Konflikt auslösen',
  'Truncate all i18n analyze tables and start a fresh translation session, no translations will be lost.'
    => 'Alle i18n Analyse Tabellen zurücksetzen und mit einer neuen Übersetzungssitzung starten, hierbei gehen keine Übersetzung verloren.',
  'Truncated the tables for the i18nEditor.'
    => 'Alle Tabellen für den i18n Editor wurden zurückgesetzt.',
  'type'
    => 'Typ',
  'Unassigned'
    => 'Nicht zugeordnet',
  'Unassigned translations'
    => 'Nicht zugeordnete Übersetzungen',
  '<em>Unassigned translations</em> can be used to translate <em>sources</em>, which are representend by a variable and does not physically exists in a program file or a template.'
    => '<em>Nicht zugeordnete Übersetzungen</em> können verwendet werden um <em>Quelltexte</em> zu übersetzen, die durch eine Variable repräsentiert werden und nicht physikalisch in einer Programmdatei oder einem Template existieren.',
  'Update'
    => 'Aktualisierung',
  'Update available!'
    => 'Aktualisierung verfügbar!',
  'updated'
    => 'aktualisiert',
  'Updated the catalog data for the extension(s) <strong>%extension%</strong>.'
    => 'Die Kataloginformationen für die Erweiterung(en) <strong>%extension%</strong> wurden aktualisiert.',
  'Updated the database settings for the CMS.'
    => 'Die Datenbank Einstellungen für das CMS wurden aktualisiert.',
  'Updated the flexContent bootstrap.include.inc for the permanent links.'
    => 'Die flexContent <var>bootstrap.include.inc</var> für die permanenten Links wurde aktualisiert.',
  'Updated the kitFramework CMS settings.'
    => 'Die kitFramework CMS Einstellungen wurden aktualisiert.',
  'Updated the kitFramework database settings.'
    => 'Die kitFramework Datenbankeinstellungen wurden aktualisiert.',
  'Updated the kitFramework email settings.'
    => 'Die kitFramework E-Mail Einstellungen wurden aktualisiert.',
  'Updated the kitFramework URL to %url%.'
    => 'Aktualisierte die kitFramework URL zu <var>%url%</var>.',
  'Updated the miniShop bootstrap.include.inc for the permanent links.'
    => 'Aktualisierte die miniShop <var>bootstrap.include.inc</var> für die permanenten Links.',
  'Updated the permalink URL for Event'
    => 'Aktualisierte die URL für permanente Links in Event.',
  'Updated the register data for the extension(s) <strong>%extension%</strong>.'
    => 'Die Registerinformationen für die Erweiterung(en) <strong>%extension%</strong> wurden aktualisiert.',
  'Updated the terms & conditions URL for Event'
    => 'Aktualisierte die URL für die allgemeinen Geschäftsbedingungen in Event',
  'Updated Translation ID %id%'
    => 'Übersetzung mit der ID %id% aktualisiert.',
  'Updates'
    => 'Aktualisierungen',
  'Usage'
    => 'Verwendung',
  'Use the entry points for an easy access'
    => 'Verwenden Sie die Zugangspunkte für einen einfachen Zugriff auf das kitFramework',
  'Use <code>~~ help ~~</code> to view the <a href="%link%">general help file for the kitCommands</a>.'
    => 'Verwenden Sie <code>~~ help ~~</code> um sich die <a href="%link%">Allgemeine Hilfe zu den kitCommands</a> anzeigen zu lassen.',
  'User roles may depend from others and can be set or extended dynamically by the kitFramework extensions. To see the roles really associated to this account if the user is authenticated use the "switch to" button.'
    => 'Benutzerrechte können von einander abhängig sein und dynamisch durch kitFramework Anwendungen erweitert werden. Um die Benutzerrechte zu sehen, die tatsächlich einem angemeldeten Anwender zugewiesen sind nutzen Sie bitte die Funktion "Zum Anwender umschalten".',
  'Username'
    => 'Benutzername',
  'Username or email address'
    => 'Benutzername oder <nobr>E-Mail</nobr> Adresse',
  'Vendor'
    => 'Anbieter',
  'View and edit the kitFramework configuration files'
    => 'Die kitFramework Konfigurationsdateien einsehen und bearbeiten',
  'View the custom translations for this installation'
    => 'Sehen Sie die angepassten Übersetzungen für diese kitFramework Installation',
  'View the general help for kitCommands'
    => 'Die allgemeine Hilfe zu den kitCommands anzeigen',
  'View the help file for %command%'
    => 'Hilfedatei für %command% anzeigen lassen',
  'View the helpfile for %command%'
    => 'Die Hilfedatei zu %command% anzeigen',
  'View the translations grouped by locale files'
    => 'Übersetzungen nach Übersetzungsdateien zusammengefasst',
  'Visit the Wiki for %command% and learn more about it!'
    => 'Besuchen Sie das Wiki zu %command% und erfahren Sie mehr über die Möglichkeiten!',
  'Waiting'
    => 'Anstehend',
  'We have send a link to your email address %email%.'
    => 'Wir haben Ihnen einen Link an Ihre E-Mail Adresse %email% gesendet.',
  'Welcome'
    => 'Herzlich Willkommen!',
  'Welcome back, %user%! Please select the entry point you want to use.'
    => 'Herzlich willkommen, %user%! Bitte wählen Sie den gewünschten Zugangspunkt.',
  'Wiki'
    => 'Wiki',
  'Yes'
    => 'Ja',
  'You are not allowed to access this resource!'
    => 'Sie sind nicht befugt auf diese Resource zuzugreifen.',
  'You execute the i18nEditor in <strong>developer mode</strong>.'
    => 'Sie führen den i18nEditor im <strong>Entwickler Modus</strong> aus.',
  'You have already an account? <a href="%login_url%">Please login</a>!'
    => 'Sie haben bereits ein Benutzerkonto? <a href="%login_url%">Melden Sie sich an</a>!',
  'You have configured flexContent as Remote Client. Please check the specified remote URLs in <var>config.flexcontent.json</var>.'
    => 'Sie haben flexContent als Remote Client eingerichtet. Bitte überprüfen Sie die in der <var>config.flexcontent.json</var> angegebenen Remote URLs.',
  'You must login as user \'%username%\'!'
    => 'Sie müssen sich als Benutzer \'%username%\' anmelden!',
  'You must solve the <strong>CONFLICT</strong> before you can change this translation record.'
    => 'Sie müssen zunächst den <strong>CONFLICT</strong> lösen bevor Sie diese Übersetzung ändern können.',
  'Your account is locked, but it seems that you have not activated your account. Please use the activation link you have received.'
    => 'Ihr Benutzerkonto ist gesperrt, es sieht allerdings so aus, als ob Sie das Konto noch nicht aktiviert haben. Bitte verwenden Sie den Aktivierungslink den Sie erhalten haben.',
  'Your account is locked, please contact the webmaster.'
    => 'Ihr Benutzerkonto ist gesperrt, bitte nehmen Sie Verbindung mit dem Webmaster auf.',
  'Your account was succesfull updated.'
    => 'Ihr Benutzerkonto wurde erfolgreich geändert.',
  'Your are not authenticated, please login!'
    => 'Sie sind nicht angemeldet, bitte authentifizieren Sie sich zunächst!',
);
