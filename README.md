
# Moodle-Block: Virtueller Lehrer

## Beschreibung
Dieser Moodle-Block zeigt ein Bild eines virtuellen Lehrers mit zwei Interaktionsoptionen:
- Textbasierter Chat mit einem KI-Modell (z. B. Gemini über OpenRouter.ai)
- (Platzhalter) Sprachinteraktion

Der Chat wird über ein externes API angebunden und ermöglicht eine direkte Kommunikation mit einem KI-Modell.

## Anforderungen
- Moodle 4.1 oder höher
- API-Zugang zu OpenRouter.ai (API-Key und Modell erforderlich)

## Installation
1. Ordner `block_virtualteacher` in das Verzeichnis `moodle/blocks/` kopieren
2. Moodle als Administrator aufrufen und die Installation abschließen
3. Den Block zu einem Kurs oder zur Startseite hinzufügen

## Konfiguration
Die Datei `api.php` enthält die Zugangsdaten und API-Konfiguration. Hier können Modell, Schlüssel und URL angepasst werden.

## Sicherheitshinweis
Für den Produktionseinsatz sollten API-Zugangsdaten nicht im Klartext gespeichert werden.
