#!/bin/sh

set -e

echo "🏁 Lancement de l'application Steam Status..."

# Installation des dépendances (si nécessaire)
composer install --no-interaction --prefer-dist --optimize-autoloader

# Création de la base si elle n'existe pas
php bin/console doctrine:database:create --if-not-exists || true

# Migration automatique
php bin/console doctrine:migrations:migrate --no-interaction

# Lancer Supervisor (FPM + nginx + Messenger)
exec /usr/bin/supervisord -c /etc/supervisord.conf
