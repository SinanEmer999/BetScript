# 🏓 GitHub Copilot Instructions for BetScript

## Projekt-Übersicht
BetScript ist eine FIETZ Points Wettplattform für das KickScript Kickerliga-System. Nutzer können auf Matches wetten, FIETZ Points verdienen und Cosmetics für ihre Avatare kaufen. Zusätzlich gibt es Casino Mini-Games (Crash, Plinko, Blackjack).

## Architektur

### Tech Stack
- **Backend**: PHP 8.0+, Slim Framework 4, Twig 3, PHP-DI 7
- **Datenspeicherung**: JSON-Dateien mit File Locking (keine SQL-Datenbank)
- **Frontend**: Vanilla JavaScript, Custom CSS (Stake.com-inspiriertes Dark Design)
- **Integration**: Liest Match- und Spieler-Daten aus KickScript JSON-Dateien

### Verzeichnisstruktur
```
BetScript/
├── app/
│   ├── Config/container.php        # DI Container Setup
│   ├── Controllers/                # HTTP Request Handler
│   ├── Models/                     # User, Bet, Cosmetic, KickerMatch
│   ├── Services/                   # Business Logic Layer
│   │   ├── DataService.php         # JSON CRUD mit File Locking
│   │   ├── UserService.php         # User Management & Auth
│   │   ├── BettingService.php      # Wett-Logik
│   │   ├── OddsService.php         # ELO-basierte Quotenberechnung
│   │   ├── CosmeticService.php     # Shop & Avatar
│   │   ├── KickScriptIntegrationService.php  # Daten von KickScript
│   │   └── Games/                  # Casino-Spiele Services
│   └── routes.php                  # Route Definitionen
├── public/
│   ├── index.php                   # Entry Point
│   └── assets/                     # CSS, JS, Bilder
├── templates/                      # Twig Templates
├── data/                          # JSON Storage (users, bets, cosmetics, games)
└── logs/                          # App Logs
```

## Code-Konventionen

### PHP Standards
- **PSR-12 Extended**: Strict PHP coding standard
- **Strict Types**: Alle Dateien mit `declare(strict_types=1);`
- **Type Declarations**: Parameter und Return Types immer angeben
- **Dependency Injection**: Constructor Injection via PHP-DI
- **Single Responsibility**: Jede Klasse/Service hat eine klare Aufgabe

### Daten-Philosophie
- **JSON als Single Source of Truth**: Alle Daten in `data/*.json`
- **File Locking**: Bei jedem Schreibvorgang LOCK_EX verwenden
- **Keine Redundanz**: Berechnete Werte (z.B. Leaderboard) on-the-fly berechnen
- **Atomare Operationen**: Load -> Modify -> Save immer zusammen

### Naming Conventions
- **Classes**: PascalCase (`UserService`, `BettingController`)
- **Methods**: camelCase (`placeBet()`, `calculateOdds()`)
- **Variables**: camelCase (`$fietzPoints`, `$matchId`)
- **Constants**: SCREAMING_SNAKE_CASE (in Klassen)
- **Array Keys**: camelCase in JSON, snake_case in DB (falls später migriert)

## Kern-Features

### 1. Wett-System
- **Quotenberechnung**: ELO-basiert + Recent Form + House Edge (5%)
- **Formel**: `expectedScore = 1 / (1 + 10^((ELO2 - ELO1) / 400))`
- **Min Odds**: 1.10x
- **Dynamische Anpassung**: Quoten ändern sich basierend auf Wettvolumen
- **Drei Wettarten**: `player1`, `player2`, `draw`

### 2. FIETZ Points Economy
- **Initial**: 1000 Points bei Registrierung
- **Daily Bonus**: 100 Points täglich
- **Verdienen**: Durch erfolgreiche Wetten (amount * odds)
- **Ausgeben**: Shop (Cosmetics), Casino-Spiele
- **Kein echtes Geld**: Rein virtuelles System

### 3. Casino Mini-Games
- **Crash**: 
  - Provably Fair RNG
  - Multiplier steigt, User casht aus bevor Crash
  - Max 1000x Multiplier
- **Plinko**: 
  - 16 Rows, Ball-Drop Simulation
  - 3 Risk Levels (low/medium/high)
  - Verschiedene Multiplier-Sets
- **Blackjack**: 
  - Standard Rules
  - Dealer steht bei 17
  - 2x Payout bei Gewinn

### 4. Avatar & Cosmetics
- **Kategorien**: hat, glasses, background, frame, badge
- **Rarity**: common, rare, epic, legendary
- **Purchase Flow**: Punkte prüfen → abziehen → Cosmetic zu User hinzufügen
- **Avatar Update**: User kann owned Cosmetics equipen

### 5. KickScript Integration
- **Data Path**: `$_ENV['KICKSCRIPT_DATA_PATH']` (default: `../kickScript/kickLiga/data`)
- **Matches**: `matches.json` lesen für verfügbare Spiele
- **Players**: `players.json` für ELO und Stats
- **Auto-Resolve**: Bei Match-Ende Wetten automatisch auflösen

## Wichtige Services

### DataService
```php
load(string $filename): array           // JSON lesen
save(string $filename, array $data): bool  // JSON schreiben mit LOCK_EX
loadUsers(): array                      // User-Objekte laden
saveUsers(array $users): bool           // User-Objekte speichern
```

### OddsService
```php
calculateOdds(string $matchId): array   // ELO-basierte Quoten berechnen
adjustOddsForBettingVolume(...)         // Dynamische Anpassung
getLiveOdds(string $matchId): ?array    // Live-Quoten während Spiel
```

### BettingService
```php
placeBet($userId, $matchId, $prediction, $amount): ?Bet
getUserBets(string $userId): array
resolveBet(string $betId, string $result): bool
resolveMatchBets(string $matchId, string $result): int
cancelBet(string $betId, string $userId): bool
```

## Frontend Design (Stake.com-Stil)

### CSS Variablen
```css
--bg-primary: #0f212e        // Haupt-Hintergrund
--bg-secondary: #1a2c38      // Cards/Navbar
--bg-tertiary: #213743       // Inputs/Buttons
--accent-primary: #00e701    // Grüner Akzent
--text-primary: #ffffff      // Haupttext
--text-secondary: #b1bad3    // Sekundärtext
--text-muted: #7c8a9e        // Muted Text
```

### UI-Komponenten
- **Cards**: Rounded (12px), Border, Hover-Effekt (translateY + Shadow)
- **Buttons**: `.btn-primary` (green), `.btn-secondary` (dark)
- **Forms**: Dark Inputs, Green Focus Border
- **Tables**: Striped Rows, Top 3 (Gold/Silver/Bronze) hervorgehoben
- **Responsive**: Mobile-First Grid Layout

## Entwickler-Workflows

### Neue Route hinzufügen
1. Controller-Methode erstellen
2. Route in `app/routes.php` registrieren
3. Twig Template erstellen
4. Controller im Container registrieren (falls neu)

### Neues Casino-Spiel
1. Service in `app/Services/Games/` erstellen
2. Game-Logik mit RNG implementieren
3. Controller-Methoden in `GamesController.php`
4. Routes registrieren
5. Twig Template + JavaScript für UI
6. Navigation in Layout hinzufügen

### Neue Cosmetic-Kategorie
1. `CosmeticService::initializeDefaultCosmetics()` erweitern
2. Shop Template aktualisieren (neue Tab)
3. Avatar-Rendering-Logik anpassen
4. Icons/Platzhalter hinzufügen

### Deployment
1. `composer install --no-dev --optimize-autoloader`
2. `.env` mit Production-Werten erstellen
3. Alle Dateien (inkl. `vendor/`) per FTP hochladen
4. DocumentRoot auf `public/` setzen
5. `data/` und `logs/` Schreibrechte geben (755)

## Sicherheit

- **Passwörter**: `password_hash()` / `password_verify()` (bcrypt)
- **XSS**: Twig auto-escaping (alle Variablen escaped)
- **Input Validation**: Type Casting, min/max Prüfungen
- **Session**: PHP Sessions für Auth
- **CSRF**: Für Production CSRF-Tokens hinzufügen
- **File Locking**: Verhindert Race Conditions bei JSON-Writes

## Performance

- **JSON Storage**: OK für <1000 User, dann DB Migration
- **Caching**: Aktuell keins, für Production Redis empfohlen
- **File Locks**: Können Bottleneck werden bei hoher Last
- **No Build Step**: Vanilla JS, keine Transpilation nötig

## Debugging

- **Logs**: `logs/` Verzeichnis prüfen
- **Debug Mode**: `APP_DEBUG=true` in `.env`
- **Browser Console**: Frontend JavaScript Errors
- **Twig Debug**: `{{ dump(variable) }}` in Templates (nur mit Debug-Mode)

## Integration Points

### KickScript Data Flow
1. `KickScriptIntegrationService::getAllMatches()` liest `matches.json`
2. `getPlayerStats()` liest `players.json` für ELO
3. `OddsService` berechnet Quoten basierend auf Spieler-Daten
4. Beim Match-Ende: `BettingService::resolveMatchBets()`

### Zukünftige Erweiterungen
- WebSocket für Live-Updates
- Webhooks von KickScript bei Match-Ende
- Achievement-System
- Leaderboards mit Seasonal Resets
- Social Features (Friends, Chat)

## Beispiele

### Wette platzieren
```php
$bettingService->placeBet(
    userId: 'usr_123',
    matchId: 'match_456',
    prediction: 'player1',
    amount: 100
);
// Returns Bet object oder null bei Fehler
```

### Quoten berechnen
```php
$odds = $oddsService->calculateOdds('match_456');
// ['player1' => 1.85, 'player2' => 2.10, 'draw' => 3.50]
```

### Cosmetic kaufen
```php
$success = $cosmeticService->purchaseCosmetic(
    userId: 'usr_123',
    cosmeticId: 'hat_crown_gold'
);
// Returns true/false
```

---

**Wichtig**: Alle Änderungen sollten die bestehenden Code-Konventionen befolgen. Bei Unsicherheiten die existierenden Services als Referenz nutzen.
