# Steam Status — Counter-Strike 2 Monitor

> 🇫🇷 Vous cherchez la version française ? [Cliquez ici](https://github.com/PxL1337/steam-status/blob/master/README.md)

![Symfony](https://img.shields.io/badge/Symfony-7.x-black?logo=symfony)
![PHP](https://img.shields.io/badge/PHP-8.3-blue?logo=php)
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

- **PHP 8.3**
- **Symfony 7.x**
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

The application can be deployed in two modes:
1. With an integrated PostgreSQL database
2. With an external PostgreSQL database

---

### 🧩 1. With Integrated Database

#### ✅ Using `docker run`

```bash
docker run -d \
  -e STEAM_API_KEY=your_steam_api_key \
  -p 8080:8080 \
  valpxl/steam-status:latest
```

- Environment variables:
  - **Required**: `STEAM_API_KEY`
  - **Optional**: `PORT` (through `-p <host>:8080`)

#### ✅ Using `docker-compose` (Portainer or CLI)

##### 📌 Example stack for **Portainer**:

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

➡️ Set the `STEAM_API_KEY` and `PORT` values directly in Portainer.

##### 🖥️ For `docker compose` via CLI:

```bash
docker compose -f docker-compose.prod.yml up -d
```

➡️ **Important**: update the values in a `.env` file at the root of the project.

---

### 🌐 2. With External Database

#### ✅ Using `docker run`

```bash
docker run -d \
  -e STEAM_API_KEY=your_steam_api_key \
  -e DATABASE_URL=postgresql://user:pass@host:port/dbname?serverVersion=15&charset=utf8 \
  -p 8080:8080 \
  valpxl/steam-status:latest
```

- Environment variables:
  - **Required**: `STEAM_API_KEY`
  - **Required**: `DATABASE_URL`
  - **Optional**: `PORT` (through `-p <host>:8080`)

#### ✅ Using `docker-compose` (Portainer or CLI)

##### 📌 Example stack for **Portainer**:

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

➡️ Set the `STEAM_API_KEY`, `DATABASE_URL` and `PORT` values directly in Portainer.

##### 🖥️ For `docker compose` via CLI:

```bash
docker compose -f docker-compose.prod.external-db.yml up -d
```

➡️ **Important**: update the values of `DATABASE_URL` and `STEAM_API_KEY` in a `.env` file at the root of the project.

- Example `.env`:

```dotenv
STEAM_API_KEY=your_steam_api_key
DATABASE_URL=postgresql://user:pass@host:5432/dbname?serverVersion=15&charset=utf8
PORT=8080
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
