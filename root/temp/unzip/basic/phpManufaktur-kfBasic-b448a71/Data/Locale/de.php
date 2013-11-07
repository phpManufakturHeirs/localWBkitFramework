<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if ('á' != "\xc3\xa1") {
    // the language files must be saved as UTF-8 (without BOM)
    throw new \Exception('The language file ' . __FILE__ . ' is damaged, it must be saved UTF-8 encoded!');
}

return array(
    'Add the extension <b>%name%</b> to the catalog.'
        => 'Die Erweiterung <b>%name%</b> wurde dem Katalog hinzugefügt.',
    'Add the extension <b>%name%</b> to the register.'
        => 'Die Erweiterung <b>%name%</b> wurde dem Register hinzugefügt.',
    'Additional information'
        => 'Zusatzinformation',

    'Bad credentials'
        => 'Die Angaben sind unvollständig oder ungültig!',

    'Can\'t create the target directory for the extension!'
        => 'Konnte das Zielverzeichnis für die Extension nicht erstellen!',
    'Can not read the extension.json for %name%!<br />Error message: %error%'
        => 'Kann die Beschreibungsdatei extension.json für die Erweiterung <b>%name%</b> nicht lesen!<br />Fehlermeldung: %error%',
    'Can not read the information file for the kitFramework!'
        => 'Kann die Beschreibungsdatei für das kitFramework nicht lesen!',
    "Can't read the the %repository% from %organization% at Github!"
        => 'Kann das Repository %repository% von %organization% auf Github nicht lesen!',
    'Can\'t create a new GUID as long the last GUID is not expired. You must wait 24 hours between the creation of new passwords.'
        => 'Es kann keine neue GUID erzeugt werden, solange die letzte noch gültig ist. Sie können das Anlegen eines neuen Passwort nur einmal innerhalb von 24 Stunden anfordern!',
    "Can't open the file <b>%file%</b>!"
        => '<p>Kann die Datei <b>%file%</b> nicht öffnen!</p>',
    "Can't read the the %repository% from %organization% at Github!"
        => 'Kann das Repository <b>%repository%</b> von der Organisation <b>%organization%</b> auf Github nicht lesen!',
    'captcha-timeout'
        => 'Zeitüberschreitung bei der CAPTCHA Übermittlung, bitte versuchen Sie es erneut.',
    'Could not move the unzipped files to the target directory.'
        => 'Konnte die entpackten Dateien nicht in das Zielverzeichnis verschieben!',
    'Create a new password' =>
        'Ein neues Password anlegen',

    'Documentation'
        => 'Dokumentation',

    'Email'
        => 'E-Mail',
    "<b>Error</b>: Can't execute the kitCommand: <i>%command%</i>"
        => '<b>Fehler</b>: Das kitCommand <i>%command%</i> konnte nicht ausgeführt werden.',
    'Error executing the kitCommand <b>%command%</b>'
        => 'Bei der Ausführung des kitCommand <b>%command%</b> ist ein Fehler aufgetreten',

    'File'
        => 'Datei',
    'First steps'
        => 'Erste Schritte',
    'Forgot your password?'
        => 'Passwort vergessen?',

    'Get in touch with the developers, receive support, tipps and tricks for %command%!'
        => 'Treten Sie mit den Entwicklern in Kontakt und erhalten Unterstützung, erfahren Tipps sowie Tricks zu %command%!',
    'Get more information about %command%'
        => 'Erfahren Sie mehr über %command%',
    'Goodbye'
        => 'Auf Wiedersehen',

    '<p>Hello %name%,<br />you have asked to create a new password for the kitFramework hosted at %server%.</p>'
        => '<p>Hallo %name%,<br />Sie haben darum gebeten ein neues Passwort für das kitFramework auf %server% zu erhalten.</p>',
    '<p>Hello %name%,</p><p>you want to change your password, so please type in a new one, repeat it and submit the form.</p><p>If you won\'t change your password just leave this dialog.</p>'
        => '<p>Hallo %name%,</p><p>Sie möchten Ihr Passwort ändern, bitte geben Sie das neue Passwort ein, wiederholen Sie es zur Sicherheit und schicken Sie das Formular ab.</p><p>Falls Sie Ihr Passwort nicht ändern möchten, verlassen Sie bitte einfach diesen Dialog.</p>',
    'Help'
        => 'Hilfe',

    '<p>If you have forgotten your password, you can order a link to create a new one.</p><p>Please type in the email address assigned to your account and submit the form.</p>'
        => '<p>Falls Sie Ihr Passwort vergessen haben, können Sie einen Link anfordern um ein neues Passwort zu erstellen.</p><p>Bitte tragen Sie die E-Mail Adresse ein, die ihrem Konto zugeordnet ist und übermitteln Sie das Formular.</p>',
    '<p>If you have not asked to create a new password, just do nothing. The link above is valid only for 24 hours and your actual password has not changed now.</p>'
        => '<p>Falls Sie kein neues Passwort angefordert haben, ignorieren Sie diese E-Mail bitte. Der o.a. Link ist lediglich für 24 Stunden gültig und ihr aktuelles Passwort wurde nicht geändert.</p>',
    'incorrect-captcha-sol'
        => 'Der übermittelte CAPTCHA ist nicht korrekt.',
    'invalid-request-cookie'
        => 'Ungültige ReCaptcha Anfrage',
    'invalid-site-private-key'
        => 'Der private Schlüssel für den ReCaptcha Service ist ungültig, prüfen Sie die Einstellungen!',
    'Issues'
        => 'Mängel',

    'kitFramework - Create new password'
        => 'kitFramework - Neues Passwort anlegen',
    'kitFramework - Login'
        => 'kitFramework - Anmeldung',
    'kitFramework - Logout'
        => 'kitFramework - Abmeldung',
    'kitFramework password reset'
        => 'kitFramework - Passwort zurücksetzen',

    'License'
        => 'Lizenz',
    'Line'
        => 'Zeile',
    'Link transmitted'
        => 'Link übermittelt',
    'List'
        => 'Liste',
    'Login' =>
        'Anmelden',
    'Logout' =>
        'Abmelden',

    'Message'
        => 'Mitteilung',
    'more information about <b>%command%</b> ...'
        => 'mehr Informationen über <b>%command%</b> ...',
    'New kitFramework release available!'
        => 'Es ist eine neue kitFramework Release verfügbar!',

    'No fitting user role dectected!'
        => 'Es wurde kein passendes Benutzerrecht gefunden',

    'Open this helpfile in a new window'
        => 'Diese Hilfedatei in einem neuen Fenster öffnen',

    'Password'
        => 'Passwort',
    'Password changed'
        => 'Passwort geändert',
    'Please check the username and password and try again!'
        => 'Bitte prüfen Sie den angegebenen Benutzernamen sowie das Passwort und versuchen Sie es erneut!',
    'Please <a href="%link%" target="_blank">comment this help</a> to improve the kitCommand <b>%command%</b>.'
        => 'Bitte <a href="%link%" target="_blank">kommentieren und ergänzen Sie diese Hilfe</a> um das kitCommand <b>%command%</b> zu verbessern.',
    '<p>Please login to the kitFramework with your username or email address and the assigned password.</p><p>Your can also use your username and password for the CMS.</p>'
        => '<p>Bitte melden Sie sich am kitFramework mit Ihrem Benutzernamen oder Ihrer E-Mail Adresse und Ihrem Passwort an.</p><p>Sie können sich auch mit Ihrem Benutzernamen und Passwort für das CMS anmelden.</p>',
    'Please report all issues and help to improve %command%!'
        => 'Bitte melden Sie alle auftretenden Probleme und helfen Sie mit %command% zu verbessern!',
    '<p>Please use the following link to create a new password:<br />%reset_url%</p>'
        => '<p>Bitte verwenden Sie den folgenden Link um ein neues Passwort anzulegen:<br />%reset_url%</p>',
    'published at'
        => 'veröffentlicht am',

    '<p>Regards<br />Your kitFramework team</p>'
        => '<p>Mit freundlichn Grüßen<br />Ihr kitFramework Team</p>',
    'Repeat Password'
        => 'Passwort wiederholen',
    'Report problems'
        => 'Fehler melden',
    'Reveal this e-mail address'
        => 'Anklicken um die vollständige E-Mail Adresse anzuzeigen',

    'Scan for installed extensions'
        => 'Nach installierten Erweiterungen suchen',
    'Scan the online catalog for available extensions'
        => 'Den online Katalog nach verfügbaren Erweiterungen durchsuchen',
    'Show a list of all installed kitCommands'
        => 'Eine Liste mit den installierten kitCommands anzeigen',
    'Sorry, but only Administrators are allowed to access this kitFramework extension.'
        => 'Ihre Berechtigung ist nicht ausreichend, nur Administratoren dürfen das kitFramework CMS Tool verwenden.',
    'Sorry, but the submitted GUID is invalid. Please contact the webmaster.'
        => 'Die übermittelte GUID ist ungültig. Bitte nehmen Sie mit dem Webmaster Kontakt auf.',
    'Submit'
        => 'Übermitteln',
    'Successfull installed the extension %extension%.'
        => 'Die Erweiterung %extension% wurde erfolgreich installiert.',
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

    'Thank you for using the kitFramework'
        => 'Vielen Dank für den Einsatz des kitFramework',
    '<p>The both passwords you have typed in does not match, please try again!</p>'
        => '<p>Die beiden Passwörter die Sie eingegeben haben stimmen nicht überein, bitte versuchen Sie es noch einmal!</p>',
    'The extension with the ID %extension_id% does not exists!'
        => 'Die Erweiterung mit der ID %extension_id% existiert nicht!',
    'The file %file% does not exists in Gist %gist_id%!'
        => 'Die Datei %file% existiert nicht im Gist %gist_id%',
    'The extension.json of <b>%name%</b> does not contain all definitions, check GUID, Group and Release!'
        => 'Die Beschreibungsdatei extension.json für die Erweiterung <b>%name%</b> enthält nicht alle Definitionen, prüfen Sie <i>GUID</i>, <i>Group</i> und <i>Release</i>!',
    '<p>The password for the kitFramework was successfull changed.</p><p>You can now <a href="%login%">login using the new password</a>.</p>'
        => '<p>Ihr Passwort für das kitFramework wurde erfolgreich geändert.</p><p>Sie können sich jetzt <a href="%login%">mit Ihrem neuen Passwort anmelden</a>.</p>',
    '<p>The password you have typed in is not strength enough.</p><p>Please choose a password at minimun 8 characters long, containing lower and uppercase characters, numbers and special chars. Spaces are not allowed.</p>'
        => '<p>Das übermittelte Passwort ist nicht stark genug.</p><p>Bitte wählen Sie ein Passwort mit mindestens 8 Zeichen Länge, mit einem Mix aus Groß- und Kleinbuchstaben, Zahlen und Sonderzeichen. Leerzeichen sind nicht gestattet.</p>',
    'The password you typed in is not correct, please try again.'
        => 'Das angegebene Passwort is nicht korrekt, bitte geben Sie es erneut ein',
    'The received extension.json does not specifiy the path of the extension!'
        => 'Die empfangene extension.json enthält nicht den Installationspfand für die Extension!',
    'The received repository has an unexpected directory structure!'
        => 'Das empfangene Repository hat eine unterwartete Verzeichnisstruktur und kann nicht eingelesen werden.',
    'The requested page could not be found!'
        => 'Die angeforderte Seite wurde nicht gefunden!',
    'The submitted GUID is expired and no longer valid.<br />Please <a href="%password_forgotten%">order a new link</a>.'
        => '<p>Die übermittelte GUID ist abgelaufen und nicht länger gültig.</p><p>Bitte <a href="%password_forgotten%">fordern Sie einen neuen Link an</a>.</p>',
    'The submitted GUID was already used and is no longer valid.<br />Please <a href="%password_forgotten%">order a new link</a>.'
        => '<p>Die übermittelte GUID wurde bereits verwendet und ist nicht mehr gültig.</p><p>Bitte <a href="%password_forgotten%">fordern Sie einen neuen Link an</a>.</p>',
    'There exists no catalog entry for the extension %name% with the GUID %guid%.'
        => 'Es existiert kein Katalog Eintrag für die Erweiterung %name% mit der GUID %guid%.',
    'There exists no user with the submitted email address.'
        => 'Die übermittelte E-Mail Adresse kann keinem Benutzer zugeordnet werden.',
    'There is no help available for the kitCommand <b>%command%</b>.'
        => 'Für das kitCommand <b>%command%</b> ist keine Hilfe verfügbar.',
    "This link enable you to change your password once within 24 hours."
        => "Dieser Link ermöglicht es Ihnen, ihr Passwort einmal innerhalb von 24 Stunden zu ändern.",
    '<p>This value is not a valid email address.</p>'
        => '<p>Es wurde keine gültige E-Mail Adresse übergeben!</p>',

    'Unknown user'
        => 'Unbekannter Benutzer',
    'Updated the catalog data for <b>%name%</b>.'
        => 'Die Katalogdaten für die Erweiterung <b>%name%</b> wurden aktualisiert.',
    'Updated the register data for <b>%name%</b>.'
        => 'Die Registrierdaten für die Erweiterung <b>%name%</b> wurden aktualisiert.',
    'Use <code>~~ help ~~</code> to view the general help file for the kitCommands.'
        => 'Verwenden Sie <code>~~ help ~~</code> um sich die allgemeine Hilfe zu den kitCommands anzeigen zu lassen.',
    'Username'
        => 'Benutzername',
    'Username or email address'
        => 'Benutzername oder <nobr>E-Mail</nobr> Adresse',

    'View the general help for kitCommands'
        => 'Die allgemeine Hilfe zu den kitCommands anzeigen',
    'View the helpfile for %command%'
        => 'Die Hilfedatei zu %command% anzeigen',
    'Visit the Wiki for %command% and learn more about it!'
        => 'Besuchen Sie das Wiki zu %command% und erfahren Sie mehr über die Möglichkeiten!',

    'We have send a link to your email address %email%.'
        => 'Wir haben Ihnen einen Link an Ihre E-Mail Adresse %email% gesendet.',
    'Welcome' =>
        'Herzlich Willkommen!',

    'You are not allowed to access this resource!'
        => 'Sie sind nicht befugt auf diese Resource zuzugreifen.',
);
