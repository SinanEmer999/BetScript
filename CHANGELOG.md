# BetScript Changelog

## [1.0.0] - 2024-12-10

### Initial Release

#### Features
- 🏆 **Wett-System**
  - ELO-basierte Quotenberechnung
  - Dynamische Odds-Anpassung
  - Drei Wettarten (Player 1, Player 2, Draw)
  - Live-Wetten Support

- ⭐ **FIETZ Points Economy**
  - 1000 Startpunkte
  - 100 Punkte täglich
  - Verdienen durch erfolgreiche Wetten
  - Ausgeben im Shop und Casino

- 🎰 **Casino Mini-Games**
  - Crash Game (Provably Fair)
  - Plinko (3 Risiko-Stufen)
  - Blackjack (Klassisch)

- 🎨 **Avatar & Cosmetics**
  - 5 Kategorien (Hats, Glasses, Backgrounds, Frames, Badges)
  - 4 Seltenheitsstufen
  - 12+ Standard-Cosmetics
  - Shop-System

- 🔗 **KickScript Integration**
  - Automatisches Match-Einlesen
  - ELO-basierte Quoten
  - Auto-Resolve bei Match-Ende

- 🎨 **Design**
  - Stake.com-inspiriertes Dark Theme
  - Responsive Layout
  - Moderne UI-Komponenten
  - Smooth Animations

#### Technical
- PHP 8.0+ mit Slim Framework 4
- Twig 3 Templating
- PHP-DI 7 Dependency Injection
- JSON-basierte Datenspeicherung
- File Locking für Concurrency
- PSR-12 Coding Standards

#### Documentation
- Comprehensive README
- Installation Guide
- AI Context Documentation
- GitHub Copilot Instructions
- API Documentation (inline)

---

### Geplante Features (v1.1.0)
- [ ] WebSocket für Live-Updates
- [ ] Leaderboards mit Seasons
- [ ] Achievement-System
- [ ] Match-Historie mit Charts
- [ ] Social Features (Friends)
- [ ] Mobile App
- [ ] Admin-Panel
- [ ] Multi-Language Support

### Known Issues
- Canvas-Animation in Plinko noch nicht implementiert
- Avatar-Rendering benötigt CSS/SVG Sprites
- CSRF-Protection fehlt (Production TODO)
- Rate Limiting fehlt (Production TODO)
