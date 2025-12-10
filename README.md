# 🎲 BetScript - FIETZ Points Betting Platform

Eine Wettplattform für das KickScript Kickerliga-System mit virtueller Währung (FIETZ Points), Cosmetics Shop und Casino Mini-Games.

![Stake.com-inspired Design](https://img.shields.io/badge/Design-Stake.com%20inspired-00e701)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4)
![Slim Framework](https://img.shields.io/badge/Slim-4-green)

## 🌟 Features

### 🏆 Wett-System
- **ELO-basierte Quoten**: Automatische Quotenberechnung basierend auf Spieler-ELO und aktueller Form
- **Dynamische Odds**: Quoten passen sich dem Wettvolumen an
- **Live-Wetten**: Echtzeit-Quoten während laufender Spiele
- **Drei Wettarten**: Spieler 1 gewinnt, Spieler 2 gewinnt, Unentschieden

### ⭐ FIETZ Points System
- **1000 Startpunkte** bei Registrierung
- **100 Punkte täglich** als Bonus
- **Punkte verdienen** durch erfolgreiche Wetten
- **Punkte ausgeben** im Shop oder bei Casino-Spielen
- **Kein echtes Geld** - rein virtuelles Belohnungssystem

### 🎰 Casino Mini-Games
- **🚀 Crash**: Steige aus bevor es crasht! Provably Fair Multiplier-Game
- **🎯 Plinko**: Wirf die Kugel und hoffe auf hohe Multiplikatoren (3 Risiko-Stufen)
- **🃏 Blackjack**: Klassisches Kartenspiel gegen den Dealer

### 🎨 Avatar & Cosmetics
- **5 Kategorien**: Hüte, Brillen, Hintergründe, Rahmen, Abzeichen
- **4 Seltenheitsstufen**: Common, Rare, Epic, Legendary
- **Individuelle Avatare**: Gestalte deinen eigenen Avatar
- **Shop-System**: Kaufe Cosmetics mit FIETZ Points

### 🔗 KickScript Integration
- Automatisches Einlesen der Matches aus KickScript
- ELO-Ratings der Spieler für Quotenberechnung
- Automatische Wett-Auflösung bei Match-Ende

## 🚀 Installation

### Voraussetzungen
- PHP 8.0 oder höher
- Composer
- Zugriff auf KickScript-Installation (für Match-Daten)

### Lokale Installation

1. **Repository klonen**
```bash
git clone https://github.com/yourusername/BetScript.git
cd BetScript
```

2. **Dependencies installieren**
```bash
composer install
```

3. **Umgebungsvariablen konfigurieren**
```bash
cp .env.example .env
```

Bearbeite `.env`:
```env
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:1338

# Pfad zu KickScript Daten
KICKSCRIPT_DATA_PATH=../kickScript/kickLiga/data

# FIETZ Points Konfiguration
INITIAL_POINTS=1000
DAILY_BONUS=100
MIN_BET=10
MAX_BET=1000
```

4. **Berechtigungen setzen**
```bash
chmod -R 755 data/ logs/
```

5. **Entwicklungsserver starten**
```bash
php -S localhost:1338 -t public
```

6. **Im Browser öffnen**
```
http://localhost:1338
```

## 📦 FTP-Deployment

1. **Alle Dateien hochladen** (inkl. `vendor/`)
2. `.env` Datei anpassen (Produktionswerte)
3. **DocumentRoot** auf `public/` Verzeichnis setzen
4. **Schreibrechte** für `data/` und `logs/` vergeben:
```bash
chmod -R 755 data/ logs/
```

### Apache .htaccess
Bereits enthalten in `public/.htaccess` und Root `.htaccess`

### Nginx Konfiguration
```nginx
server {
    listen 80;
    server_name betscript.yourdomain.com;
    root /path/to/BetScript/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 🏗️ Architektur

### Technologie-Stack
- **Backend**: Slim Framework 4 (PHP)
- **Templating**: Twig 3
- **DI Container**: PHP-DI 7
- **Datenspeicherung**: JSON-Dateien (File Locking)
- **Frontend**: Vanilla JavaScript + Custom CSS

### Verzeichnisstruktur
```
BetScript/
├── app/
│   ├── Config/          # DI Container
│   ├── Controllers/     # HTTP Request Handler
│   ├── Models/          # Datenmodelle
│   ├── Services/        # Business Logic
│   │   └── Games/       # Casino-Spiele
│   └── routes.php       # Route-Definitionen
├── public/
│   ├── index.php        # Entry Point
│   └── assets/          # CSS, JS, Bilder
├── templates/           # Twig Templates
├── data/               # JSON Datenspeicher
└── logs/               # Logs
```

## 🎮 Nutzung

### Registrierung
1. Besuche `/register`
2. Erstelle Account (Username, Email, Passwort)
3. Erhalte automatisch 1000 FIETZ Points

### Wetten platzieren
1. Navigiere zu "Wetten"
2. Wähle ein kommendes Match
3. Klicke auf gewünschtes Ergebnis (Spieler 1, Unentschieden, Spieler 2)
4. Gib Wetteinsatz ein
5. Bestätige Wette

### Casino spielen
1. Wähle ein Spiel (Crash, Plinko, Blackjack)
2. Setze FIETZ Points ein
3. Spiele und gewinne!

### Cosmetics kaufen
1. Besuche den Shop
2. Durchsuche Kategorien
3. Kaufe Items mit FIETZ Points
4. Rüste Cosmetics in deinem Profil aus

## 🔧 Konfiguration

### Quoten-Berechnung
Die Quoten basieren auf:
- **ELO-Ratings** der Spieler
- **Aktuelle Form** (letzte 5 Spiele)
- **House Edge** (5%)
- **Wettvolumen** (dynamische Anpassung)

Formel:
```php
expectedScore = 1 / (1 + 10^((ELO_diff) / 400))
odds = 0.95 / probability  // mit 5% House Edge
```

### Casino-Spiele
- **Crash**: Provably Fair RNG, max 1000x Multiplier
- **Plinko**: 16 Reihen, 3 Risiko-Level, ~96% RTP
- **Blackjack**: Standard Regeln, Dealer steht bei 17

## 📊 Datenmodelle

### User
```php
{
    "id": "usr_...",
    "username": "player1",
    "fietzPoints": 5000,
    "cosmetics": ["hat_crown_gold"],
    "avatar": {
        "hat": "hat_crown_gold",
        "glasses": null,
        ...
    },
    "totalBets": 50,
    "wonBets": 28,
    "totalWinnings": 3500
}
```

### Bet
```php
{
    "id": "bet_...",
    "userId": "usr_...",
    "matchId": "match_...",
    "prediction": "player1",
    "amount": 100,
    "odds": 1.85,
    "potentialWin": 185,
    "status": "pending"
}
```

## 🛠️ Development

### Code-Standards
- **PSR-12** Coding Standard
- **Type Declarations**: Strict Types
- **Dependency Injection**: Constructor Injection
- **Separation of Concerns**: MVC Pattern

### Tests ausführen
```bash
# TODO: PHPUnit Tests hinzufügen
composer test
```

### Neues Casino-Spiel hinzufügen
1. Service erstellen in `app/Services/Games/YourGameService.php`
2. Controller-Methoden in `GamesController.php`
3. Routes in `routes.php` registrieren
4. Template in `templates/games/yourgame.twig`
5. Navigation in Layout hinzufügen

## 🔒 Sicherheit

- ✅ Password Hashing (bcrypt)
- ✅ XSS Protection (Twig Auto-Escaping)
- ✅ Input Validation
- ✅ File Locking (Race Condition Prevention)
- ⚠️ CSRF Protection (für Produktion empfohlen)
- ⚠️ Rate Limiting (für Produktion empfohlen)

## 📈 Performance

- **JSON Storage**: Geeignet für <1000 User
- **Empfehlung für Skalierung**: Migration zu MySQL/PostgreSQL
- **Caching**: Redis/Memcached für Production empfohlen
- **File Locking**: Kann Bottleneck werden bei hoher Last

## 🤝 Beitragen

Contributions sind willkommen! Bitte:
1. Fork das Repository
2. Erstelle einen Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit deine Changes (`git commit -m 'Add AmazingFeature'`)
4. Push zum Branch (`git push origin feature/AmazingFeature`)
5. Öffne einen Pull Request

## 📝 Lizenz

Dieses Projekt ist für den privaten/internen Gebrauch bestimmt.

## 🙏 Credits

- **Design Inspiration**: Stake.com
- **Kickerliga System**: KickScript
- **Framework**: Slim Framework

## 📧 Support

Bei Fragen oder Problemen:
- 📮 Issues auf GitHub erstellen
- 📧 Email: your-email@example.com

---

**Hinweis**: Nur mit FIETZ Points - kein echtes Geld! Verantwortungsvoll spielen! 🎮
