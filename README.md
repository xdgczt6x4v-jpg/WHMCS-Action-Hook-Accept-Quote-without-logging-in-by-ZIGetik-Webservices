# WHMCS Action Hook: Accept Quote without Login
Ermöglicht Kunden, WHMCS-Angebote direkt per E-Mail-Link zu akzeptieren – ohne Login erforderlich.

📋 Beschreibung
Dieser Action Hook erweitert WHMCS um eine praktische Funktion: Kunden können Angebote (Quotes) direkt über einen sicheren Link in der Angebots-E-Mail akzeptieren, ohne sich im Kundenportal anmelden zu müssen. Nach der Annahme wird automatisch eine Rechnung erstellt und versendet.
Hauptfunktionen

✅ Ein-Klick-Akzeptierung: Kunden akzeptieren Angebote direkt aus der E-Mail
🔒 Sicher: Hash-basierte Verifizierung (Quote-ID + Client-ID + E-Mail)
🧾 Automatische Rechnungserstellung: Rechnung wird sofort erstellt und per E-Mail versendet
💬 Benutzerfreundliches Modal: Ansprechende Bestätigungsmeldung nach Akzeptierung
🌐 Mehrsprachig: Einfach anpassbar für verschiedene Sprachen
🔄 WHMCS 9.0+ kompatibel: Optimiert für moderne PHP-Versionen (8.1, 8.2)

🚀 Installation

Lade die Datei zigetik_accept_quote_without_login.php hoch nach:

   /path/to/whmcs/includes/hooks/

Der Hook wird automatisch aktiviert – keine weitere Konfiguration nötig
Teste die Funktion durch Versenden eines Angebots

📊 Funktionsweise
Schritt 1: E-Mail-Link generieren
Beim Versand der Angebots-E-Mail wird der Standard-Link durch einen sicheren Hash-Link ersetzt:
Original: https://deinwhmcs.de/viewquote.php?id=123
Neu: https://deinwhmcs.de/index.php?qhash=abc123def456-123
Schritt 2: Angebot akzeptieren
Wenn der Kunde auf den Link klickt:

Hash wird validiert
Angebot wird über WHMCS API akzeptiert
Rechnung wird erstellt
Rechnung wird per E-Mail versendet
Bestätigungsmodal wird angezeigt

Schritt 3: Bestätigung
Der Kunde sieht eine freundliche Bestätigungsnachricht mit nächsten Schritten.
⚙️ Technische Details
Sicherheit

Hash-Generierung: strrev(md5(quote_id + client_id + email)) + quote_id
SQL-Injection-Schutz: Verwendung von Laravel Query Builder (Capsule)
XSS-Schutz: htmlspecialchars() für alle Ausgaben
Input-Validierung: Strikte Prüfung aller GET-Parameter

Hooks

EmailPreSend: Modifiziert Angebots-E-Mail mit sicherem Link
ClientAreaHeadOutput: Verarbeitet Hash und akzeptiert Angebot

Kompatibilität

✅ WHMCS 9.0+
✅ PHP 8.1, 8.2, 8.3
✅ Bootstrap 4 & 5 Templates
✅ jQuery und Vanilla JavaScript

🎨 Anpassung
Sprache ändern
Passe die Texte im Modal an (Zeile ~85-95):
javascriptif (titleEl) titleEl.innerHTML = 'Quote #{$quoteId} Accepted';
if (bodyEl) {
    bodyEl.innerHTML = '<div class="container">...DEIN TEXT HIER...</div>';
}
```

### E-Mail-Template
Der Hook nutzt das Standard-Template `Quote Delivery with PDF`. Du kannst dieses in WHMCS unter:
**Setup → Email Templates → Quote Delivery with PDF** anpassen.

### Modal-Design
CSS kann über dein WHMCS-Template angepasst werden. Das Modal verwendet Standard-Bootstrap-Klassen.

## 📝 Beispiel-Output

### Activity Log
```
Quote #123 accepted via email link
Invoice #456 created from Quote #123 acceptance
```

### Modal-Nachricht (Deutsch)
```
Angebot #123 akzeptiert

Hallo Max,

vielen Dank für die Annahme des Angebots #123 (Webhosting Premium).
So geht es weiter:
- Sie erhalten in Kürze die Rechnung per E-Mail
- Nach Zahlungseingang aktivieren wir Ihre Bestellung umgehend

Bei Fragen stehen wir Ihnen gerne zur Verfügung: Kontakt aufnehmen
🔧 Troubleshooting
Link funktioniert nicht

Prüfe ob der Hook in /includes/hooks/ liegt
Stelle sicher, dass das Angebot noch nicht akzeptiert wurde
Überprüfe den Hash-Algorithmus (Quote-ID muss korrekt sein)

Modal wird nicht angezeigt

Prüfe Browser-Konsole auf JavaScript-Fehler
Stelle sicher, dass Bootstrap korrekt geladen ist
Teste mit verschiedenen WHMCS-Templates

Rechnung wird nicht erstellt

Prüfe Activity Log für Fehlermeldungen
Stelle sicher, dass Invoice-Settings korrekt sind
Überprüfe E-Mail-Template-Einstellungen

📄 Lizenz
Dieses Projekt basiert auf dem ursprünglichen Konzept von Katamaze und wurde von ZIGetik Webservices für WHMCS 9.0+ erweitert und abgesichert.
Open Source – Frei verwendbar unter MIT-Lizenz.
🤝 Beitragen
Pull Requests sind willkommen! Für größere Änderungen öffne bitte zuerst ein Issue.
👨‍💻 Autor
ZIGetik Webservices
https://zigetik.com

Basierend auf dem Original-Konzept von Katamaze
⭐ Support
Bei Fragen oder Problemen:

Öffne ein GitHub Issue
Kontaktiere uns: kontakt@zigetik.com

Gefällt dir das Projekt? Gib uns einen ⭐ auf GitHub!
