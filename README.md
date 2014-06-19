Hypercharge Zahlungsschnittstelle für Shopware	
=====================

Die Hypercharge-Zahlungsschnittstelle ermöglicht es Ihnen, Ihren Kunden eine große Reihe an Zahlungsmethoden in Ihrem Shopware-Shop anzubieten – sowohl Off- als auch Online. Sensible Daten werden dabei sicher und vertraulich auf der Hypercharge-Plattform behandelt.

**Shopware-Versionen**: Das Plugin ist aktuell für Shopware 4.0.x bis 4.2.x getestet und freigegeben. 


---------
 1. Installation des Plugins, Kurzbeschreibung
---------
 1. Das Plugin haben Sie entweder bereits als ZIP-Datei zur Verfügung gestellt bekommen oder Sie laden in diesem Repository die Dateien, inklusive des Pfades Frontend herunter und packen die Dateien selbst als ZIP. Das Verzeichnis Frontend muss mit gepackt werden.
 
 2. Loggen Sie sich in Ihr Shopware-Backend ein

 3. Wählen Sie das Feld Einstellungen > Plugin-Manager und gehen Sie auf Plugin manuell hinzufügen 

 4. Wählen Sie das Hypercharge Payment Plugin (im Zip-Format) aus und fügen sie es über den Button Plugin hochladen ihrem Backend hinzu.

 5. Das Plugin wird installiert und es erscheint eine Meldung über die erfolgreiche Installation im Backend. Außerdem öffnet sich ein Popup mit den Möglichkeiten, das Plugin zu aktivieren oder zu deaktivieren
5.1 Falls Sie es zu einem späteren Zeitpunkt aktivieren wollen, finden Sie das Hypercharge Payment Plugin in der Auflistung Inaktive Plugins unter dem Menüpunkt Meine Erweiterungen > Alle Erweiterungen oder einfach über die Suchfunktion
5.2 Durch einen Click auf das Stift-Symbol in der Zeile des Hypercharge Payment Plugins lässt sich Ihr Plugin aktivieren und konfigurieren.

---------
 1.1	Wo finde und wie aktiviere ich das Hypercharge-Plugin im Backend konkret?
---------


Zur Aktivierung/Deaktivierung Ihres Hypercharge -Plugins gelangen Sie über die Shopware-Backend-Felder **Einstellungen > Plugin Manager** und dort unter den Rubriken **Meine Erweiterungen > Alle Erweiterungen** oder einfach über die Suchfunktion. 

In der rechten Navigation finden Sie das Plugin in der Rubrik **Inaktive Plugins**. Über den Stift (Plugin editieren) neben dem Hypercharge Payment Plugin gelangen Sie zur Detailseite und können das Plugin für Ihren Shop aktivieren und konfigurieren.

Die reine Aktivierung erfolgt über die Checkbox **Aktiv: Plugin aktivieren**. Die Anleitung zur detaillierten Konfiguration finden Sie im Folgenden. Bitte betätigen Sie im Anschluss an Ihre Aktivierung oder Konfiguration den Button **Plugin-Einstellungen speichern** um die Änderungen aktiv werden zu lassen.

---------
 1.2	Konfiguration
---------


*Im Folgenden erhalten Sie eine detaillierte Beschreibung der Konfigurationsmöglichkeiten für Ihr Hypercharge Payment Plugin.*

>Als *Minimalkonfiguration* ist lediglich die Einstellung des Hypercharge Kanals sowie die Angabe der Kreditkartentypen, die Sie Ihren Kunden anbieten möchten, notwendig.
 
**Verwenden Testmodus:** zum Testen Ihrer Konfiguration (z.B. für Entwicklungsumgebungen) können Sie im Dropdownmenü die Auswahl auf "Ja" einstellen. In diesem Fall wird das Tool zum Testsystem von Hypercharge verklinkt sein. Zur Verwendung des Plugins im Live-Betrieb wählen Sie bitte "Nein".

**Hypercharge Kanal:** diese Angabe entnehmen Sie bitte Ihren von Hypercharge gelieferten Daten. Die Eingabe der Kanal-Informationen sollte dabei das folgende Format haben: CCY,User,Password,Channel_id (durch Kommata getrennt und ohne Leerschritte). Für den Fall, dass Sie mehrere Kanäle verwenden, trennen Sie diese Bitte durch einen Zeilenumbruch (Enter), so dass sie wie folgt getrennt sind:

CCY1,user,password,channel_id_1
CCY2,user,password,channel_id_2

**Seiten-Layout des Zahlungsvorgangs:** mit diesem Dropdown-Menü können Sie auswählen, ob Ihr Kunde den Zahlungsvorgang auf Ihrer Shopware-Seite durchführen kann durch die Auswahl von "Integration der Bezahlseite via iFrame", oder ob er für die Transaktion an Hypercharge weitergeleitet wird ("Weiterleitung zu Hypercharge").

**iFrame Höhe:** sofern Sie für das vorgenannte Item ‚Seiten-Layout des Zahlungsvorgangs‘ Integration der ‚Bezahlseite via iFrame‘ gewählt haben, wird dieser Punkt für Sie relevant. Mit der Einstellung ‚iFrame Höhe‘ wird das HTML-Element iFrame (auch Inlineframe) konfiguriert, das auf Ihrer Shopware-Seite eingebettet wird. Sofern Sie für das Item „Seiten-Layout des Zahlungsvorgangs“ Integration der Bezahlseite via iFrame gewählt haben, wird dieser Punkt für Sie relevant. Es handelt sich dabei um ein Fenster, das auf der Checkout-Seite dargestellt wird. Hier können Sie die Höhe des iFrames auf der Checkout-Seite in Pixeln bestimmen angeben. Zu empfehlen ist die Standardeinstellung der Höhe von 720 Pixeln.

**iFrame Breite:** mit dieser Einstellung wird die Breite des oben genannten HTML-Elements iFrame bestimmt. Zu empfehlen ist die Standardeinstellung der Breite von 959 Pixeln.

**Kreditkartentypen:** hier wählen Sie eine oder mehrere Kreditkartentypen aus einem Dropdown-Menü aus, die Sie Ihren Kunden zur Zahlung anbieten möchten. Zur Anwahl stehen American Express, Visa, MasterCard, Discover, JCB und "Andere" zur Verfügung. Ihr Kunde kann dadurch im Frontend einen der angebotenen Kreditkartentypen auswählen.

**Editieren der Rechnungsadresse durch den Nutzer zulassen:** hiermit können Sie Ihrem Kunden das Editieren seiner Rechnungsadresse anbieten. Sofern Sie "Ja" wählen, wird von Hypercharge eine neue Seite dargestellt, in der die Rechnungsdaten mit den Standard-Rechnungsdaten aus Shopware zur Editierung angezeigt werden. Als Standardeinstellung ist hier "Nein" gesetzt.

**Ausgabe von Logdateien:** mit der Auswahl von "Ja" und "Nein" bestimmen Sie, ob für die Zahlungsprozesse Logdateien ausgegeben werden und auf Ihren Server geschrieben werden. In der Logdatei werden die Daten erfasst, die von Shopware zu Hypercharge gesendet werden. Zudem werden darin Angaben dazu aufgeführt, welche Daten von Hypercharge gespeichert werden und es werden Rückmeldungen oder Hinweise von Hypercharge aufgeführt. 

----------
