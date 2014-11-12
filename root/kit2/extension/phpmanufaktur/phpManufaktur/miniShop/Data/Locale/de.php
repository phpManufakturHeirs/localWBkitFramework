<?php

/**
 * kitFramework::miniShop
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
  'a company or a organization'
    => 'eine Firma, Organisation, Behörde oder Verein',
  'a private person'
    => 'eine Privatperson, Familie',
  'Account owner'
    => 'Kontoinhaber',
  'Add article to cart'
    => 'Artikel dem Warenkorb hinzufügen',
  'Add article to the shopping basket'
    => 'Artikel zum Warenkorb hinzufügen',
  'Added article <strong>%article%</strong> to the basket.'
    => 'Der Artikel <strong>%article%</strong> wurde dem Warenkorb hinzugefügt.',
  'Advance payment'
    => 'Vorkasse',
  'Article'
    => 'Artikel',
  'Article delete checkbox'
    => 'diesen Artikel unwiderruflich löschen',
  'Article group'
    => 'Artikel Gruppe',
  'Article group delete checkbox'
    => 'diese Artikelgruppe und alle verbundenen Artikel unwiderruflich löschen',
  'Article groups'
    => 'Artikelgruppen',
  'Article image'
    => 'Abbildung',
  'Article image folder gallery'
    => 'verwende Ordner der Abbildung für eine Galerie',
  'Article limit'
    => 'max. Bestellmenge',
  'Article list'
    => 'Artikel Übersicht',
  'Article name'
    => 'Artikelname',
  'Article price'
    => 'Preis',
  'Article price type'
    => 'Preisangabe',
  'Article value added tax'
    => 'Umsatzsteuer (Artikel)',
  'Article variant name'
    => 'Variante, Bezeichner',
  'Article variant name 2'
    => 'Variante, Bezeichner',
  'Article variant values'
    => 'Variante, Werte',
  'Article variant values 2'
    => 'Variante, Werte',
  'article_variant_name'
    => 'Variante, Bezeichner',
  'article_variant_name_2'
    => 'Variante, Bezeichner',
  'Articles'
    => 'Artikel',
  'At least you must specify one payment method!'
    => 'Sie müssen mindestens eine Zahlungsmethode festlegen!',
  'Available'
    => 'sofort lieferbar',
  'Available date'
    => 'lieferbar ab',
  'Available date order'
    => 'kann bestellt werden, lieferbar ab',
  'Available soon'
    => 'in Kürze lieferbar',
  'Available soon order'
    => 'kann bestellt werden, in Kürze lieferbar',
  'Back to the shopping basket'
    => 'Zurück zum Warenkorb',
  'Bank name'
    => 'Bankname',
  'Banking account'
    => 'Bankkonto',
  'Base configuration'
    => 'Basis Einstellungen',
  'Base configuration delete checkbox'
    => 'diese Basis Einstellung und alle verbundenen Artikelgruppen unwiderruflich löschen',
  'Base configurations'
    => 'Basis Einstellungen',
  'Base id'
    => 'ID',
  'Base name'
    => 'Basis Bezeichnung',
  'Basket'
    => 'Warenkorb',
  'BIC'
    => 'BIC',
  'Bic'
    => 'BIC',
  'Changed quantity for the article <strong>%article%</strong> to <strong>%quantity%</strong>.'
    => 'Die Bestellmenge für den Artikel <strong>%article%</strong> wurde auf <strong>%quantity%</strong> geändert.',
  'Changed the quantity for article <strong>%article%</strong> from <strong>%old_quantity%</strong> to <strong>%new_quantity%</strong>.'
    => 'Für den Artikel <strong>%article%</strong> wurde die Anzahl von <strong>%old_quantity%</strong> zu <strong>%new_quantity%</strong> geändert.',
  'Configure your PayPal account'
    => 'PayPal Konto konfigurieren',
  'Confirm order now'
    => 'Ich bestelle hiermit diese(n) Artikel',
  'Confirmation timestamp'
    => 'Bestätigung',
  'Confirmed'
    => 'Bestätigt',
  'contact_id'
    => 'Kontakt ID',
  'Contact ID <strong>%contact_id%</strong> assigned to order ID <strong>%order_id%</strong> does no longer exists!'
    => 'Die Kontakt ID <strong>%contact_id%</strong>, die der Bestellung mit der ID <strong>%order_id%</strong> zugeordnet ist, existiert nicht mehr!',
  'Contact ID <strong>%contact_id%</strong> assigned to order ID <strong>%order_id%</strong> is marked as <strong>DELETED</strong>.'
    => 'Die Kontakt ID <strong>%contact_id%</strong>, die der Bestellung mit der ID <strong>%order_id%</strong> zugeordnet ist, ist als <strong>GELÖSCHT</strong> markiert,',
  'Create a new article'
    => 'Einen neuen Artikel anlegen',
  'Create a new article group'
    => 'Eine neue Artikelgruppe erstellen',
  'Create a new category'
    => 'Eine neue Kategorie anlegen',
  'Create a new extra field'
    => 'Ein neues Zusatzfeld anlegen',
  'Create a new miniShop base'
    => 'Neue Basis Einstellung anlegen',
  'Create a new tag'
    => 'Einen neuen #Hashtag anlegen',
  'Create a new title'
    => 'Eine neue Anrede anlegen',
  'Create customer'
    => 'Kunde anlegen',
  'Create or edit a customer record'
    => 'Kunden Datensatz anlegen oder bearbeiten',
  'Create or edit article for the miniShop'
    => 'Artikel anlegen oder bearbeiten',
  'Currency'
    => 'Währung',
  'Currency iso'
    => 'Währung',
  'Currently are no articles available.'
    => 'Momentan befinden sich keine Artikel im Shop.',
  'Customer list'
    => 'Kundenliste',
  'Define and edit base configurations for the miniShop'
    => 'Anlegen und Bearbeiten von Basis Einstellungen für den miniShop',
  'Define and edit the article groups for the miniShop'
    => 'Erstellen und Bearbeiten von Artikelgruppen für den miniShop',
  'Define and edit the articles for the miniShop'
    => 'Erstellen und Bearbeiten von Artikeln für den miniShop',
  'Description long'
    => 'Beschreibung',
  'Description short'
    => 'Anreisser',
  'Determined by each article'
    => 'Festlegung durch jeden Artikel',
  'Each value in a separate line, use <key>SHIFT</key>+<key>ENTER</key>'
    => 'Jeder Wert in einer eigenen Zeile, verwenden Sie <key>UMSCHALT</key>+<key>EINGABE</key>',
  'Edit customer'
    => 'Kunde bearbeiten',
  'Flatrate for shipping and handling'
    => 'Versandkostenpauschale',
  'Form action'
    => 'Aktion',
  'Free of shipping costs'
    => 'Versandkostenfrei',
  'Gross price'
    => 'Bruttopreis',
  'help_minishop_json'
    => '<p>Konfigurationsdatei für den <a href="https://kit2.phpmanufaktur.de/miniShop" target="_blank">miniShop</a>.</p><p>Wichtige Abschnitte/Einstellungen:</p><p><var>nav_tabs</var>: mit <var>default</var> legen Sie fest, welcher Navigationsreiter beim Aufrufen des miniShop über die Admin-Tools oder die Zugangspunkte angezeigt wird.</p><p><var>locale</var>: verfügbare Sprachen für den miniShop.</p><p><var>currency</var>: Einstellungen für die verschiedenen Währungen.</p><p><var>images</var>: Einstellungen für die Artikelbilder.</p><p><var>permanentlink</var>: das Verzeichnis unterhalb des CMS Stammverzeichnis, das als Basis für alle permanenten Links des miniShop verwendet wird. Falls Sie hier Änderungen vornehmen, müssen Sie anschließend <em>Setup</em> ausführen.</p><p><var>libraries</var>: vom miniShop automatisch geladene Bibliotheken und Stylesheets. Setzen Sie <var>enabled</var> auf <var>false</var> um das Laden generell zu unterbinden.</p><p><strong><var>banking_account</var></strong>: Angaben zu dem Bankkonto, dass der miniShop verwenden soll - wird zwingend benötigt, wenn Sie <strong>Vorkasse</strong> verwenden.</p><p><var>basket</var>: mit <var>lifetime_hours</var> legen Sie fest, wie viele Stunden der Warenkorb eines Kunden zwischengespeichert werden soll bevor er verfällt.</p><p><var>order</var>: über <var>admin/list/max_days</var> stellen Sie ein, für wieviele Tage Bestellungen angezeigt werden sollen, ältere Bestellungen werden ausgeblendet um die Liste übersichtlich zu halten.</p><p><var>contact</var>: Steuerung der Kontaktdialoge im Frontend und Backend (siehe <a href="https://github.com/phpManufaktur/kfContact/wiki" target="_blank">Contact Wiki</a> für weitere Informationen).</p>',
  'I have read and accept the <a href="%url%" target="_blank">terms and conditions</a>'
    => 'Ich habe die <a href="%url%" target="_blank">Allgemeinen Geschäftsbedingungen</a> gelesen und akzeptiert',
  'IBAN'
    => 'IBAN',
  'Iban'
    => 'IBAN',
  'Indicate that the contact has used the miniShop'
    => 'Der Kontakt hat den miniShop verwendet',
  'Information about the miniShop extension'
    => 'Informationen über die miniShop Erweiterung',
  'Invalid quantity, ignored article.'
    => 'Ungültige Mengenangabe, Artikel ignoriert.',
  'Logo'
    => 'Logo',
  'Minishop'
    => 'miniShop',
  'miniShop - About'
    => 'miniShop - Information',
  'miniShop for the kitFramework'
    => 'miniShop für das kitFramework',
  'miniShop order by advance payment'
    => 'miniShop Bestellung - Vorauszahlung',
  'miniShop order by on account payment'
    => 'miniShop Bestellung - auf Rechung',
  'miniShop order by PayPal'
    => 'miniShop Bestellung über PayPal',
  'Missing the parameter <em>action</em>!'
    => 'Vermisse den Parameter <em>action</em>!',
  'Missing the parameter <var>sub_action</var>!'
    => 'Vermisse den Parameter <var>sub_action</var>!',
  'Net price'
    => 'Nettopreis',
  'net price without tax'
    => 'zzgl. Umsatzsteuer',
  'Next step'
    => 'Nächster Schritt',
  'No shipping'
    => 'Kein Versand',
  'Not available'
    => 'nicht lieferbar',
  'Number'
    => 'Nummer',
  'On account'
    => 'auf Rechnung',
  'Ooops, unknown form action: %action%'
    => 'Uuuuups, unbekannte Aktion für das Formular: %action%',
  'Order'
    => 'Bestellung',
  'Order by advance payment'
    => 'Bestellung durch Vorauszahlung',
  'Order by on account payment'
    => 'Bestellung auf Rechnung',
  'Order by PayPal'
    => 'Bestellung über PayPal',
  'Order for'
    => 'Bestellung für',
  'Order list'
    => 'Bestellungen',
  'Order minimum price'
    => 'Mindestbestellpreis',
  'Order now'
    => 'Jetzt bestellen',
  'Order number'
    => 'Bestellnummer',
  'Order timestamp'
    => 'Bestellung, Zeitstempel',
  'Order total'
    => 'Gesamtbetrag',
  'Orders'
    => 'Bestellungen',
  'Owner'
    => 'Inhaber',
  'Pay now'
    => 'Jetzt bezahlen',
  'Payment method'
    => 'Zahlungsweise',
  'Payment methods'
    => 'Zahlungsmethoden',
  'Paypal'
    => 'PayPal',
  'PayPal'
    => 'PayPal',
  'Paypal Settings'
    => 'PayPal Einstellungen',
  'Permanent link'
    => 'Permanenter Link',
  'Pickup by the customer'
    => 'Selbstabholer',
  'Please configure your banking account before using the payment method <em>Advance Payment</em>.'
    => 'Bitte richten Sie Ihr Bankkonto ein bevor Sie die Zahlungsmethode <em>Vorkasse</em> verwenden.',
  'Please configure your paypal account before using the payment method <em>PayPal</em>'
    => 'Bitte richten Sie Ihr PayPal Konto ein bevor Sie die Zahlungsmethode <em>PayPal</em> verwenden.',
  'Please create a article group to start with your miniShop!'
    => 'Bitte erstellen Sie eine Artikelgruppe um mit Ihrem miniShop zu starten!',
  'Please create a article to start with your miniShop!'
    => 'Bitte legen Sie einen Artikel an um mit Ihrem miniShop zu starten!',
  'Please create a base configuration to start with your miniShop!'
    => 'Bitte erstellen Sie eine Basis Einstellung um mit Ihrem miniShop zu starten!',
  'Please define a permanent link for this article!'
    => 'Bitte definieren Sie einen permanenten Link für diesen Artikel!',
  'Please fill in all requested fields before submitting the form!'
    => 'Bitte füllen Sie alle erforderlichen Felder aus, bevor Sie das Formular übermitteln!',
  'Please submit a article ID!'
    => 'Bitte übermitteln Sie eine Artikel ID!',
  'Price'
    => 'Preis',
  'Publish date'
    => 'Im Shop ab',
  'Quantity'
    => 'Menge',
  'Quantity to order'
    => 'Bestellmenge',
  'Reason'
    => 'Verwendungszweck',
  'Refresh the shopping basket'
    => 'Den Warenkorb aktualisieren',
  'Remove image'
    => 'Abbildung entfernen',
  'Removed article <strong>%article%</strong> from your shopping basket'
    => 'Der Artikel <strong>%article%</strong> wurde aus dem Warenkorb entfernt.',
  'Removed the article <strong>%article%</strong> from the basket.'
    => 'Der Artikel <strong>%article%</strong>wurde aus dem Warenkorb entfernt.',
  'Sandbox'
    => 'Sandbox',
  'Sandbox mode'
    => 'Sandbox Modus',
  'Save'
    => 'Speichern',
  'Select article image'
    => 'Abbildung auswählen',
  'select the highest shipping cost'
    => 'die höchsten Versandkosten wählen',
  'select the lowest shipping cost'
    => 'die niedrigsten Versandkosten wählen',
  'Seo description'
    => 'SEO: Description',
  'Seo keywords'
    => 'SEO: Keywords',
  'Seo title'
    => 'SEO: Title',
  'Shipping & handling'
    => 'Verpackung und Versand',
  'Shipping article'
    => 'Versandkostentyp: Artikel',
  'Shipping cost'
    => 'Versandkosten',
  'Shipping costs %costs% %currency%'
    => 'Versandkosten %costs% %currency%',
  'Shipping flatrate'
    => 'Versandkostenpauschale',
  'Shipping type'
    => 'Versandkostentyp',
  'Shipping value added tax'
    => 'Umsatzsteuer (Versandkosten)',
  'Specify your banking account for the usage with advance payment'
    => 'Richten Sie Ihr Bankkonto für die Verwendung mit Vorkasse ein',
  'Subtotal'
    => 'Zwischensumme',
  'Succesful created a new article group'
    => 'Es wurde eine neue Artikelgruppe angelegt.',
  'Succesful created a new miniShop Base'
    => 'Es wurde erfolgreich eine neue miniShop Basis Einstellung angelegt.',
  'Successful inserted a new article.'
    => 'Der neue Artikel wurde erfolgreich angelegt.',
  'Successful updated the article.'
    => 'Der Artikel wurde erfolgreich aktualisiert.',
  'Sum include %vat%% value add tax = %vat-total% %currency%.'
    => 'Betrag enthält %vat%% Umsatzssteuer = %vat-total% %currency%.',
  'Sum total'
    => 'Gesamtbetrag',
  'sum-up the shipping costs'
    => 'alle Versandkosten addieren',
  'Switch to the article list'
    => 'Zur Artikelliste wechseln',
  'Target page link'
    => 'Zielseite im CMS',
  'Teaser'
    => 'Anreisser',
  'Terms conditions link'
    => 'AGB, Seite im CMS',
  'Thank you for confirming your email address. Your order will be processed as soon as possible.'
      => 'Vielen Dank für die Bestätigung Ihrer eMail Adresse. Ihre Bestellung wird baldmöglichst bearbeitet.',
  'Thank you for the order, we have send you a confirmation mail.'
    => 'Vielen Dank für Ihre Bestellung, wir haben Ihnen eine Bestätigung zugesendet.',
  'Thank you for the order. We have send you a email with a confirmation link, please use this link to finish your order.'
    => 'Vielen Dank für Ihre Bestellung. Wir haben Ihnen eine E-Mail mit einem Bestätigungslink zugesendet, bitte verwenden Sie diesen Link um Ihre Bestellung abzuschließen.',
  'The article group <strong>%group%</strong> does not exist, please check the kitCommand!'
    => 'Die Artikelgruppe <strong>%group%</strong> existiert nicht, bitte prüfen Sie das kitCommand!',
  'The article group has successful updated.'
    => 'Die Artikelgruppe wurde erfolgreich aktualisiert.',
  'The article group with the ID %id% has successfull deleted'
    => 'Die Artikelgruppe mit der ID %id% wurde gelöscht.',
  'The article has not changed.'
    => 'Der Artikel wurde nicht geändert.',
  'The article with the ID %id% has successfull deleted'
    => 'Der Artikel mit der ID %id% wurde erfolgreich gelöscht',
  'The banking account information has not changed.'
    => 'Die Informationen zu dem Bankkonto wurden nicht geändert.',
  'The banking account information has updated'
    => 'Die Informationen zum Bankkonto wurden aktualisiert.',
  'The base configuration <strong>%base%</strong> does not exist, please check the kitCommand!'
    => 'Die Basis Einstellung <strong>%base%</strong> existiert nicht, bitte prüfen Sie das kitCommand!',
  'The base configuration with the ID %id% has successfull deleted'
    => 'Die Basis Einstellung mit der ID %id% wurde erfolgreich gelöscht.',
  'The contact with the ID %contact_id% does no longer exists!'
    => 'Der Kontakt mit der ID %contact_id% existiert nicht mehr!',
  'The image %image% was successfull inserted.'
    => 'Die Abbildung %image% wurde eingefügt.',
  'The image was successfull removed.'
    => 'Die Abbildung wurde entfernt.',
  'The miniShop Base has successful updated.'
    => 'Die miniShop Basis Einstellung wurde erfolgreich aktualisiert.',
  'The name <strong>%name%</strong> is already in use, please select another one.'
    => 'Der Bezeichner <strong>%name%</strong> wird bereits verwendet, bitte wählen Sie einen anderen Bezeichner.',
  'The order list is empty!'
    => 'Es liegen keine Bestellungen vor.',
  'The payment at PayPal was canceled'
    => 'Die Zahlung über PayPal wurde abgebrochen.',
  'The payment at PayPal was successful'
    => 'Die Zahlung über PayPal war erfolgreich.',
  'The PayPal payment was successfull. We will send you a confirmation mail as soon we receive the automated confirmation from PayPal.'
    => 'Die Bezahlung über PayPal war erfolgreich. Wir senden Ihnen eine Bestätigung per E-Mail sobald wir die automatische Zahlungsinformation von PayPal erhalten haben.',
  'The PayPal settings has not changed.'
    => 'Die PayPal Einstellungen wurden nicht geändert.',
  'The PayPal settings has updated'
    => 'Die PayPal Einstellungen wurden aktualisiert.',
  'The permanent link <strong>/%link%</strong> is already in use by another article, please select an alternate one.'
    => 'Der permanente Link <strong>/%link%</strong> wird bereits von einem anderen Artikel verwendet, bitte legen Sie einen anderen Link fest.',
  'The selected article is already in your basket.'
    => 'Dieser Artikel befindet sich bereits in Ihrem Warenkorb.',
  'The short description can not be empty!'
    => 'Die Kurzbeschreibung darf nicht leer sein!',
  'There exists more than one base configurations, so you must set a base or a group as parameter!'
    => 'Es existiert mehr als eine Basis Einstellung, Sie müssen eine Basis oder eine Gruppe als Parameter festlegen!',
  'There exists no order with the ID %order_id%!'
    => 'Es existiert keine Bestellung mit der ID %order_id%!',
  'There was no image selected.'
    => 'Es wurde keine Abbildung ausgewählt.',
  'Timestamp'
    => 'Zeitstempel',
  'Token'
    => 'Token',
  'Total amount'
    => 'Gesamtbetrag',
  'Unknown <var>sub_action</var>: <strong>%sub_action%</strong>!'
    => 'Unbekannte <var>sub_action</var>: <strong>%sub_action%</strong>!',
  'Updated your shopping basket'
    => 'Ihr Warenkorb wurde aktualisiert.',
  'value add tax %vat%%'
    => 'zzgl. %vat%% Umsatzsteuer',
  'VAT'
    => 'Umsatzsteuer',
  'View all orders you have received'
    => 'Übersicht der Bestellungen die Sie erhalten haben',
  'View Order'
    => 'Bestellung',
  '<p>We are sorry, but there exists already a pending order of date <strong>%date%</strong>. Please confirm or discard this order before creating a new one.</p><p>We can <a href="%link%" target="_parent">send you again the confirmation mail</a>.</p>'
    => '<p>Entschuldigung, aber es existiert noch eine unbestätigte Bestellung vom <strong>%date%</strong>. Bitte bestätigen oder verwerfen Sie diese Bestellung bevor Sie eine neue aufgeben.</p><p>Wir können Ihnen die <a href="%link%" target="_parent">Bestätigungsmail erneut zusenden</a>.</p>',
  'Your basket contain %count% articles'
    => 'Ihr Warenkorb enthält %count% Artikel',
  'Your basket contain one article'
    => 'Ihr Warenkorb enthält einen Artikel',
  'Your basket does not contain any article'
    => 'Ihr Warenkorb ist leer',
  'Your miniShop order'
    => 'Ihre miniShop Bestellung',
  'Your order'
    => 'Ihre Bestellung',
  'Your shopping basket'
    => 'Ihr Warenkorb',
  'Your shopping basket has not changed.'
    => 'Ihr Warenkorb wurde nicht verändert.',
  'Your shopping basket is empty.'
    => 'Ihr Warenkorb ist leer.',
  
);
