#!/bin/bash

# BetScript Quick Setup Script
# Run this script after cloning the repository

echo "🎲 BetScript - Quick Setup"
echo "=========================="
echo ""

# Check if composer is installed
if ! command -v composer &> /dev/null
then
    echo "❌ Composer ist nicht installiert!"
    echo "   Bitte installiere Composer von https://getcomposer.org"
    exit 1
fi

echo "✅ Composer gefunden"

# Install dependencies
echo ""
echo "📦 Installiere Dependencies..."
composer install

if [ $? -ne 0 ]; then
    echo "❌ Fehler beim Installieren der Dependencies!"
    exit 1
fi

echo "✅ Dependencies installiert"

# Copy .env file
echo ""
if [ ! -f .env ]; then
    echo "📝 Erstelle .env Datei..."
    cp .env.example .env
    echo "✅ .env Datei erstellt"
    echo "⚠️  Bitte passe KICKSCRIPT_DATA_PATH in .env an!"
else
    echo "⏭️  .env Datei existiert bereits"
fi

# Create directories
echo ""
echo "📁 Erstelle Verzeichnisse..."
mkdir -p data logs
chmod -R 755 data logs
echo "✅ Verzeichnisse erstellt"

# Initialize data
echo ""
echo "🔧 Initialisiere Daten..."
php bin/init.php

if [ $? -ne 0 ]; then
    echo "❌ Fehler bei der Initialisierung!"
    exit 1
fi

echo ""
echo "✨ Setup abgeschlossen!"
echo ""
echo "🚀 Starte den Server mit:"
echo "   php -S localhost:1338 -t public"
echo ""
echo "🌐 Öffne im Browser:"
echo "   http://localhost:1338"
echo ""
echo "📚 Weitere Infos:"
echo "   README.md - Vollständige Dokumentation"
echo "   INSTALL.md - Installations-Guide"
echo ""
