# BetScript Installation & Setup

## Schnellstart

### 1. Dependencies installieren
```bash
cd BetScript
composer install
```

### 2. Environment konfigurieren
```bash
cp .env.example .env
```

Passe `.env` an (besonders `KICKSCRIPT_DATA_PATH`):
```env
KICKSCRIPT_DATA_PATH=../kickScript/kickLiga/data
```

### 3. Initialisierung
```bash
php bin/init.php
```

Dies erstellt:
- `data/` Verzeichnis mit JSON-Dateien
- Standard-Cosmetics
- Optional: Demo-User (wenn `CREATE_DEMO_USER=true` in `.env`)

### 4. Server starten
```bash
php -S localhost:1338 -t public
```

### 5. Im Browser öffnen
```
http://localhost:1338
```

## Erste Schritte

1. **Registrieren** unter `/register`
   - Erhalte 1000 FIETZ Points

2. **Auf Matches wetten** unter `/betting/matches`
   - Wähle ein Match
   - Platziere Wette

3. **Casino spielen** unter `/games/*`
   - Crash, Plinko oder Blackjack

4. **Cosmetics kaufen** unter `/shop`
   - Kaufe Items mit FIETZ Points

## FTP-Deployment

1. **Build für Production**
```bash
composer install --no-dev --optimize-autoloader
```

2. **Alle Dateien hochladen** (inkl. `vendor/`)

3. **`.env` anpassen** (Production-Werte)

4. **Berechtigungen setzen**
```bash
chmod -R 755 data/ logs/
```

5. **DocumentRoot** auf `public/` setzen

6. **Init-Script ausführen**
```bash
php bin/init.php
```

## Troubleshooting

### "Class not found" Fehler
```bash
composer dump-autoload
```

### Keine Matches verfügbar
- Prüfe `KICKSCRIPT_DATA_PATH` in `.env`
- Stelle sicher, dass KickScript Matches hat

### Berechtigungsfehler
```bash
chmod -R 755 data/ logs/
```

### JSON-Dateien beschädigt
Lösche und neu erstellen:
```bash
rm -rf data/*.json
php bin/init.php
```

## Development

### Code-Style prüfen
```bash
# TODO: PHP CodeSniffer hinzufügen
composer cs-check
```

### Logs anzeigen
```bash
tail -f logs/app.log
```

## Nützliche Befehle

### Demo-User erstellen
Füge in `.env` hinzu:
```env
CREATE_DEMO_USER=true
```
Dann:
```bash
php bin/init.php
```

### Cosmetics neu initialisieren
```bash
php bin/init-cosmetics.php
```

### Alle Daten löschen (Reset)
```bash
rm -rf data/*.json
php bin/init.php
```

## Support

Bei Problemen siehe:
- `README.md` für vollständige Dokumentation
- `.ai-docs/project-context.md` für technische Details
- GitHub Issues

---

Viel Spaß beim Wetten! 🎲
