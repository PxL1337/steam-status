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

L'application peut être déployée en deux variantes :
1. Avec base de données intégrée (PostgreSQL dans le conteneur)
2. Avec une base de données externe (PostgreSQL déjà disponible sur un autre hôte)

---

### 🧩 1. Version avec base de données intégrée

#### ✅ Via `docker run`

```bash
docker run -d \
  -e STEAM_API_KEY=your_steam_api_key \
  -p 8080:8080 \
  valpxl/steam-status:latest
```

- Variables d’environnement :
  - **Obligatoire** : `STEAM_API_KEY`
  - **Optionnelle** : `PORT` (via `-p <host>:8080`)

#### ✅ Via `docker-compose` (Portainer ou ligne de commande)

##### 📌 Exemple de stack pour **Portainer** :

```yaml
services:
  app:
    image: valpxl/steam-status:latest
    container_name: steam-status-app
    ports:
      - "${PORT:-46001}:8080"
    environment:
      STEAM_API_KEY: "${STEAM_API_KEY}"
      DATABASE_URL: "postgresql://symfony:symfony@postgres:5432/symfony?serverVersion=15&charset=utf8"
    entrypoint: ["sh", "/entrypoint.sh"]
    depends_on:
      - postgres

  postgres:
    image: postgres:15-alpine
    container_name: steam-status-db
    environment:
      POSTGRES_DB: symfony
      POSTGRES_USER: symfony
      POSTGRES_PASSWORD: symfony
    volumes:
      - steamstatus_pgdata:/var/lib/postgresql/data

volumes:
  steamstatus_pgdata:
```

➡️ Pensez à configurer les variables `STEAM_API_KEY` et `PORT` dans l’interface Portainer.

##### 🖥️ Pour `docker compose` en ligne de commande :

```bash
docker compose -f docker-compose.prod.yml up -d
```

➡️ **Important** : modifiez les variables dans un fichier `.env` à la racine du projet.

---

### 🌐 2. Version avec base de données externe

#### ✅ Via `docker run`

```bash
docker run -d \
  -e STEAM_API_KEY=your_steam_api_key \
  -e DATABASE_URL=postgresql://user:pass@host:port/dbname?serverVersion=15&charset=utf8 \
  -p 8080:8080 \
  valpxl/steam-status:latest
```

- Variables d’environnement :
  - **Obligatoire** : `STEAM_API_KEY`
  - **Obligatoire** : `DATABASE_URL`
  - **Optionnelle** : `PORT` (via `-p <host>:8080`)

#### ✅ Via `docker-compose` (Portainer ou ligne de commande)

##### 📌 Exemple de stack pour **Portainer** :

```yaml
services:
  app:
    image: valpxl/steam-status:latest
    container_name: steam-status-app
    ports:
      - "${PORT:-46001}:8080"
    environment:
      DATABASE_URL: "${DATABASE_URL}"
      STEAM_API_KEY: "${STEAM_API_KEY}"

    entrypoint: ["sh", "/entrypoint.sh"]
```

➡️ Pensez à configurer les variables `STEAM_API_KEY`, `DATABASE_URL` et `PORT`(optionnel) dans l’interface Portainer.

##### 🖥️ Pour `docker compose` en ligne de commande :

```bash
docker compose -f docker-compose.prod.external-db.yml up -d
```

➡️ **Important** : modifiez les variables `STEAM_API_KEY`, `DATABASE_URL` et `PORT`(optionnel) dans un fichier `.env` à la racine du projet.

- Exemple `.env` :

```dotenv
STEAM_API_KEY=your_steam_api_key
DATABASE_URL=postgresql://user:pass@host:5432/dbname?serverVersion=15&charset=utf8
PORT=8080
```

---

## 🧪 Tests

Pour exécuter la suite de tests PHPUnit en local, installez **PHP** et **Composer**.
Installez les dépendances puis lancez les tests :

```bash
composer install
./vendor/bin/phpunit
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
