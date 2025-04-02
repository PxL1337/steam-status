# Steam Status — Counter-Strike 2 Monitor

> 🇬🇧 Looking for the English version? [Click here](https://github.com/PxL1337/steam-status/blob/master/README.en.md)

![Symfony](https://img.shields.io/badge/Symfony-7.x-black?logo=symfony)
![PHP](https://img.shields.io/badge/PHP-8.3-blue?logo=php)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-blue?logo=postgresql)
![Chart.js](https://img.shields.io/badge/Chart.js-UX-red?logo=chartdotjs)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-purple?logo=bootstrap)
![License: Custom](https://img.shields.io/badge/License-Custom-lightgrey)
[![Docker Hub](https://img.shields.io/docker/pulls/valpxl/steam-status?label=Docker%20Hub)](https://hub.docker.com/r/valpxl/steam-status)

Application Symfony permettant de visualiser en temps réel l’état de Counter-Strike 2 : version, services, joueurs, serveurs et charge réseau mondiale.

---

## 🎮 Fonctionnalités

- Rafraîchissement automatique des données
- Deux thèmes : Ancien Steam et Steam Actuel
- Version actuelle de CS2 et détection des mises à jour < 24h
- Statut des services Steam/CS2 :
  - Matchmaking
  - Sessions de jeu
  - Communauté Steam
  - Classements
- Graphiques interactifs sur 24h :
  - Joueurs en ligne
  - Serveurs en ligne
  - Joueurs en matchmaking
  - Temps de recherche moyen
- Liste des régions avec charge et capacité des serveurs

---

## ⚙️ Stack technique

- **PHP 8.3**
- **Symfony 7.x**
- **PostgreSQL**
- **Twig & Bootstrap**
- **Symfony UX Turbo, Chart.js, Icons**
- **flag-icons**

---

## 🖼️ Aperçu

### Informations générales

![Informations générales](https://raw.githubusercontent.com/PxL1337/steam-status/master/Docs/Assets/Readme/FR/Informations_générales.png)

### État du matchmaking

![Matchmaking](https://raw.githubusercontent.com/PxL1337/steam-status/master/Docs/Assets/Readme/FR/Matchmaking.png)

### Carte des datacenters

![Datacenters](https://raw.githubusercontent.com/PxL1337/steam-status/master/Docs/Assets/Readme/FR/Datacenters.png)

---

## 🚀 Déploiement Docker

### 1. Mode standalone (local ou public)

```bash
docker compose -f docker-compose.prod.yml up --build -d
```

- Accès par défaut : `http://localhost:8080`
- Variables d’environnement :
  - `STEAM_API_KEY`
  - `PORT` (facultatif, 8080 par défaut)
- La base de données est persistée.
- Les migrations Doctrine sont appliquées automatiquement.

### 2. Base de données externe

```bash
docker compose -f docker-compose.external-db.yml up --build -d
```

Exemple `.env` :

```dotenv
DATABASE_URL=pgsql://user:pass@host:5432/dbname?serverVersion=15
STEAM_API_KEY=your_steam_api_key
```

### 3. Déploiement via Portainer (YunoHost)

- Importez `docker-compose.prod.yml` dans Portainer
- Redirigez un domaine avec l’app `Redirect` vers `http://127.0.0.1:<port>`

---

## 📦 Utilisation avec Make

Pour simplifier l'utilisation en local ou sur un serveur :

```bash
make up        # Lancer l'application avec base intégrée
make up-ext    # Lancer avec base externe
make build     # Rebuild les conteneurs
make down      # Stopper les conteneurs
make clean     # Supprimer volumes & conteneurs
make bash      # Entrer dans le conteneur
make help      # Affiche la liste des commandes disponibles
```
---

## 🧵 Worker Symfony Messenger

La file `scheduler_default` est automatiquement consommée via `supervisord`.  
Elle exécute `app:update-steam-status` toutes les minutes.

---

## 🤝 Contribuer

Les contributions sont les bienvenues !  
Merci de respecter les règles du projet, et **ne modifiez pas la carte de crédits visible dans l’application**.

---

## 📄 Licence

Voir [LICENSE](LICENSE)  
> Ce projet est libre d'utilisation, avec une restriction : **la carte de crédits renvoyant vers [PxL1337](https://github.com/PxL1337) ne doit pas être modifiée ni supprimée.**
