# 🐳 Déploiement Docker de Steam Status

Cette application Symfony permet d'afficher en temps réel des statistiques sur Counter-Strike 2 :  
📊 nombre de joueurs, serveurs, matchmaking, version de l'application, état des services Steam et charge des datacenters.

## 🚀 Déploiement

Deux options de déploiement sont possibles :

---

### 🔧 1. Avec base de données intégrée (PostgreSQL)

```bash
cp .env .env.local # si besoin
docker compose up --build
```

Accessible via : http://localhost:8080  
Modifiez le port via la variable d’environnement `PORT`.

---

### 🌐 2. Avec base de données externe

Créez un fichier `.env` contenant :

```dotenv
DATABASE_URL=pgsql://user:password@host:port/dbname?serverVersion=15
STEAM_API_KEY=your_steam_api_key
```

Puis :

```bash
docker compose -f docker-compose.external-db.yml up --build
```

---

### 🧵 Worker Symfony Messenger

La file `scheduler_default` est automatiquement consommée via `supervisord`.  
Elle exécute la commande `app:update-steam-status` toutes les minutes.

---

## 📦 Construction et publication de l’image

Cette image est automatiquement construite et poussée sur Docker Hub à chaque `push` sur la branche `main`.

Vous pouvez la retrouver ici :  
➡️ https://hub.docker.com/r/valpxl/steam-status

---

## 🛠️ Personnalisation

- Port HTTP : `PORT=8080` (modifiable)
- Clé API Steam : `STEAM_API_KEY`
- DB interne ou externe via `DATABASE_URL`

---

## 🇬🇧 English version

This Symfony app displays real-time stats about Counter-Strike 2:  
📊 players online, matchmaking, app version, Steam service status, server load by region.

See deployment instructions in this README above. Docker Compose is provided for both local DB and external DB scenarios.
