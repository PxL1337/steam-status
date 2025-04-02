# Steam Status — Counter-Strike 2 Monitor

> 🇫🇷 Vous cherchez la version française ? [Cliquez ici](README.md)

![Symfony](https://img.shields.io/badge/Symfony-6.x-black?logo=symfony)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?logo=php)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-blue?logo=postgresql)
![Chart.js](https://img.shields.io/badge/Chart.js-UX-red?logo=chartdotjs)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-purple?logo=bootstrap)
![License: Custom](https://img.shields.io/badge/License-Custom-lightgrey)
[![Docker Hub](https://img.shields.io/docker/pulls/valpxl/steam-status?label=Docker%20Hub)](https://hub.docker.com/r/valpxl/steam-status)

Symfony application to monitor real-time data about Counter-Strike 2: version info, service status, player & server stats, and global server load per region.

---

## 🎮 Features

- Automatic data refresh
- Two themes: Old Steam and Current Steam
- Current CS2 version and update detection (within 24h)
- Steam/CS2 service status:
  - Matchmaking
  - Game Sessions
  - Steam Community
  - Leaderboards
- Interactive graphs (last 24h):
  - Online players
  - Online servers
  - Players searching
  - Average matchmaking time
- Region list with server load & capacity

---

## ⚙️ Tech stack

- **Symfony 6.x**
- **PostgreSQL**
- **Twig & Bootstrap**
- **Symfony UX Turbo, Chart.js, Icons**
- **flag-icons**

---

## 🖼️ Preview

### General Informations

![General Informations](https://raw.githubusercontent.com/PxL1337/steam-status/master/Docs/Assets/Readme/EN/General_Informations.png)

### Matchmaking Status

![Matchmaking](https://raw.githubusercontent.com/PxL1337/steam-status/master/Docs/Assets/Readme/EN/Matchmaking.png)

### Datacenter Map

![Datacenters](https://raw.githubusercontent.com/PxL1337/steam-status/master/Docs/Assets/Readme/EN/Datacenters.png)

---

## 🚀 Docker Deployment

### 1. Standalone (local or public)

```bash
docker compose -f docker-compose.prod.yml up --build -d
```

- Default access: `http://localhost:8080`
- Environment variables:
  - `STEAM_API_KEY`
  - `PORT` (optional, default is 8080)
- Database is persisted
- Doctrine migrations run automatically

### 2. With external database

```bash
docker compose -f docker-compose.external-db.yml up --build -d
```

Example `.env`:

```dotenv
DATABASE_URL=pgsql://user:pass@host:5432/dbname?serverVersion=15
STEAM_API_KEY=your_steam_api_key
```

### 3. Portainer / YunoHost Deployment

- Import `docker-compose.prod.yml` into Portainer
- Use YunoHost Redirect app to forward your domain to `http://127.0.0.1:<port>`

---

## 📦 Using Make

To simplify local or server usage:

```bash
make up        # Start app with built-in DB
make up-ext    # Start with external DB
make build     # Rebuild containers
make down      # Stop containers
make clean     # Remove volumes & containers
make bash      # Enter the app container
make help      # List available commands
```

---

## 🧵 Messenger Worker

The `scheduler_default` queue is automatically consumed using `supervisord`.  
It runs `app:update-steam-status` every minute.

---

## 🤝 Contributing

Feel free to contribute!  
Please respect the project rules, and **do not remove or alter the "Credits" card linking to [PxL1337](https://github.com/PxL1337)**.

---

## 📄 License

See [LICENSE](LICENSE)  
> Free to use and modify, except for the "Credits" card linking to the author's GitHub which must remain intact.
